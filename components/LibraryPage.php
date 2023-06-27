<?php

namespace Pensoft\Library\Components;

use Cms\Classes\Theme;
use Backend\Facades\BackendAuth;
use \Cms\Classes\ComponentBase;
use Pensoft\Library\Classes\ZipFiles;
use Pensoft\Library\Models\Library;

class LibraryPage extends ComponentBase
{
	public $loggedIn;

    public function onRun()
    {
        $this->addJs('assets/js/def.js');
        $this->prepareVars();

		// by default users are not logged in
		$this->loggedIn = false;

		// then if getUser returns other value than NULL then our user is logged in
		if (!empty(BackendAuth::getUser())) {
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

    public function getRecords()
    {
        // Extract only needed parameters
        $options = request()->only(['page', 'perPage', 'type', 'search']);

        // Adding some defaults
        $options = array_merge([
            'page' => '1',
            'perPage' => '15',
            'type' => '0',
            'search' => request()->get('search'),
        ], $options);

        // Initiate the query
        $query = Library::isVisible();

        // Apply the search scope if search query exists
        if (!empty($options['search'])) {
            $query = $query->search($options['search']);
        }

        // Apply the filter if filter exists
        if (!empty($options['type'])) {
            $query = $query->filterByType($options['type']);
        }

        // Get the paginated result
        $query->paginate($options['perPage'], $options['page'])->appends(request()->query());

        return $query;
    }

    public function prepareVars()
    {
        // Extract only needed parameters
        $options = request()->only(['page', 'perPage', 'type', 'search']);

        // Adding some defaults
        $options = array_merge([
            'page' => '1',
            'perPage' => '15',
            'type' => '0',
            'search' => request()->get('search'),
        ], $options);

        // Initiate the query
        $query = Library::isVisible();

        // Apply the search scope if search query exists
        if (!empty($options['search'])) {
            $query = $query->search($options['search']);
        }

        // Apply the filter if filter exists
        if (!empty($options['type'])) {
            $query = $query->filterByType($options['type']);
        }

        if ($options['type'] == 1){
            $library = $query->sortBy('title', 'asc');
        } else {
            $library = $query->sortBy('title', 'desc');
        }

        // Get the paginated result
        $library = $library->paginate($options['perPage'], $options['page'])->appends(request()->query());

        // store the default sort based on the selected type
        $defaultSort = Library::defaultSort();

        $this->page['records'] = $library;
        $this->page['sortOptions'] = Library::$allowSortingOptions;
        $this->page['sortTypesOptions'] = (new Library())->getSortTypesOptions();
        $this->page['total_file_size_bites'] = $library->reduce(function ($carry, $item) {
            if ($item->file) {
                return $carry + $item->file->file_size;
            }
            return $carry;
        }, 0);
        $this->page['total_file_size'] = $this->page['total_file_size_bites'];

        $this->page['searchQuery'] = $options['search'];
        $this->page['currentType'] = $options['type'];
        $this->page['currentSort'] = $defaultSort;
    }


    public function onSortRecords()
    {
        // Extract the sort
        $options = post('Filter', []);

        extract(
            array_merge([
                'sort' => 'created_at desc',
            ], $options)
        );

        $library = $this->getRecords();

        // Apply the sort if sort option exists
        if($sort){
            $parts = explode(' ', $sort);
            list($sortField, $sortDirection) = $parts;
            $library = $library->sortBy($sortField, $sortDirection);
        }

        $this->page['records'] = $library->get();
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
