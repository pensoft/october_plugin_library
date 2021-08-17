<?php

namespace Pensoft\Library\Components;

use \Cms\Classes\ComponentBase;
use Pensoft\Library\Classes\ZipFiles;
use Pensoft\Library\Models\Library;

class LibraryPage extends ComponentBase
{
    public function onRun()
    {
        $this->addJs('assets/js/def.js');
        $this->prepareVars();
    }

    public function defineProperties()
    {
        return [
            'templates' => [
                'title' => 'Select templates',
                'type' => 'dropdown',
                'default' => 'template2'
            ],
            'has_search' => [
                'title' => 'Display search in documents form',
                'type' => 'checkbox',
                'default' => false
            ],
			'no_records_message' => [
				'title' => 'No records message',
				'description' => 'Message to be displeyed when no listems are added',
				'default' => 'No records found',
			],
        ];
    }

    public function getTemplatesOptions()
    {
        return [
            'template1' => 'Template 1',
            'template2' => 'Template 2',
            'template3' => 'Template 3',
        ];
    }

    public function componentDetails()
    {
        return [
            'name' => 'LibraryPage',
            'description' => 'Displays a collection of libraries.'
        ];
    }

    public function prepareVars()
    {
        $options = post('Filter', []);

        $library = Library::isVisible()->listFrontEnd($options);

		if($query = get('query')){
			$library = $library->where('title', 'iLIKE', '%' . $query . '%')
				->orwhere('authors', 'iLIKE', '%' . $query . '%')
				->orwhere('journal_title', 'iLIKE', '%' . $query . '%')
				->orwhere('publisher', 'iLIKE', '%' . $query . '%')
			;
		}

        $this->page['records'] = $library->get();
        $this->page['sortOptions'] = Library::$allowSortingOptions;
        $this->page['sortTypesOptions'] = (new Library())->getSortTypesOptions();
        $this->page['total_file_size_bites'] = $this->page['records']->reduce(function ($carry, $item) {
            if ($item->file) {
                return $carry + $item->file->file_size;
            }
            return $carry;
        }, 0);
        $this->page['total_file_size'] = $this->page['total_file_size_bites'];
    }

    public function onFilterRecords()
    {
        $this->prepareVars();
    }

    public function onDownloadAll()
    {
        $zip = (new ZipFiles(new Library))->downloadFiles();
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=publications.zip");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($zip);
        exit;
    }

    public function hasLibrary(){
        return Library::exists();
    }
}
