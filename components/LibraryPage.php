<?php

namespace Pensoft\Library\Components;

use Cms\Classes\Theme;
use Backend\Facades\BackendAuth;
use \Cms\Classes\ComponentBase;
use Pensoft\Library\Classes\ZipFiles;
use Pensoft\Library\Models\Library;
use function Pensoft\Library\{exists, exists_with, human_filesize};

class LibraryPage extends ComponentBase
{
    /**
     * by default users are not logged in
     */
	public $loggedIn = false;

    const DOWNLOAD_FILENAME = 'publications.zip';
    
    public function onRun()
    {
        $this->addJs('assets/js/def.js');
        $this->prepareVars();

		// then if getUser returns other value than NULL then our user is logged in
		if (exists(BackendAuth::getUser())) {
			$this->loggedIn = true;
		}
        
        $this->page['themeName'] = Theme::getActiveTheme()->getConfig()['name'];
        
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
            'add_file_name' => [
                'title' => 'Add file name to open pdf link',
                'type' => 'checkbox',
                'default' => true
            ],
			'no_records_message' => [
				'title' => 'No records message',
				'description' => 'Message to be displayed when no list items are added',
				'default' => 'No records found',
			],
            'redirect_to_download_page' => [
                'title' => 'Download file via file-download page',
                'type' => 'checkbox',
                'default' => false
            ],
        ];
    }

    public function getTemplatesOptions()
    {
        return [
            'template1' => 'Template 1',
            'template2' => 'Template 2',
            'template3' => 'Template 3',
            'template4' => 'Template 4',
            'template5' => 'Template 5',
            'template6' => 'Template 6',
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
        // Extract only needed parameters
        $options = request()->only(['page', 'perPage', 'sort', 'type', 'search']);

        // Adding some defaults
        $options = array_merge([
            'page' => "1",
            'perPage' => "15",
            'sort' => 'title asc',
            'type' => "0",
            'search' => request()->get('search'),
        ], $options);

//        var_dump($options);
        // Initiate the query
        $libraries = new Library();
        $libraries = $libraries->isVisible();

        // Apply the search scope if search query exists
        if (exists($search = $options['search'])) {
            $libraries = $libraries->search($search);
        }

        // Apply the filter if filter exists
        if (exists($type = $options['type'])) {
            $libraries = $libraries->filterByType($type);
        }

        // Apply the sort if sort option exists
        if (exists($sort = $options['sort'])) {
            $parts = explode(' ', $sort);
            list($sortField, $sortDirection) = $parts;

            if(exists_with(strtolower($sortDirection), ['asc', 'desc'], true)){
                $libraries = $libraries->orderBy($sortField, $sortDirection);
            }
        }

        // Get the paginated result
        $libraryRecords = $libraries->paginate($options['perPage'], $options['page'])->appends(request()->query());

        $sumFileSizes = function ($carry, $item) {
            if (isset($item->file->file_size)) {
                return $carry + $item->file->file_size;
            }
            return $carry;
        };
        // Assigning to the page variable
        $this->page['records'] = $libraryRecords;

        $this->page['sortOptions'] = Library::$allowSortingOptions;
        $this->page['sortTypesOptions'] = (new Library())->getSortTypesOptions();
        $this->page['total_file_size_bites'] = $libraryRecords->reduce($sumFileSizes, 0);
        $this->page['total_file_size_human'] = human_filesize($this->page['total_file_size_bites']);
        $this->page['searchQuery'] = $options['search'];
        $this->page['currentType'] = $options['type'];
        $this->page['currentSort'] = $options['sort'];
    }


    public function onFilterRecords()
    {
        $this->prepareVars();
    }


    public function onDownloadAll()
    {
        $zip = (new ZipFiles(new Library))->downloadFiles();
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=".self::DOWNLOAD_FILENAME);
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($zip);
        exit;
    }

    public function hasLibrary(){
        return Library::exists();
    }
    
}
