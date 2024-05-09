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

    /**
     * Component initialization.
     * Sets up JS, prepares vars for the component, and checks user authentication status.
     */
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

    /**
     * Defines the component's properties.
     *
     * @return array The properties array.
     */
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
            'milestones_filter' => [
                'title' => 'Enable and disable milestones filtration',
                'type' => 'checkbox',
                'default' => false
            ],
            'features_filter' => [
                'title' => 'Enable and disable features filtration',
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

    /**
     * Prepares the variables needed by the component's default.htm template.
     *
     * This method fetches library items based on applied filters, sorting, and pagination settings,
     * and prepares them for display.
     */
    protected function prepareVars()
    {
        $options = $this->getRequestOptions();
        $query = $this->applyFilters($options);

        $library = $query->paginate($options['perPage'], $options['page'])->appends(request()->query());

        $this->page['records'] = $library;
        $this->setPageVariables($library, $options);
    }

    /**
     * Retrieves request parameters and provides defaults.
     *
     * This method dynamically sets the default sort order based on the document type.
     *
     * @return array The array of request options including page, perPage, type, sort, and search.
     */
    protected function getRequestOptions()
    {
        $type = request()->get('type', '0');
        $defaultSort = $type == '1' ? 'title asc' : 'year desc';

        return request()->only(['page', 'perPage', 'search']) + [
            'page' => '1',
            'perPage' => '15',
            'type' => $type,
            'sort' => request('Filter.sort', $defaultSort),
            'search' => request()->get('search'),
        ];
    }

    /**
     * Applies filters based on the request options to the library items query.
     *
     * @param array $options The array of request options.
     * @return \October\Rain\Database\Builder The query builder after filters have been applied.
     */
    protected function applyFilters($options)
    {
        $query = Library::isVisible();

        if (!empty($options['search'])) {
            $query = $query->search($options['search']);
        }

        if (!empty($options['type'])) {
            $query = $query->filterBy($options['type']);
        }

        if (!empty($options['sort'])) {
            [$sortField, $sortDirection] = explode(' ', $options['sort'], 2);
            $query = $query->sortBy($sortField, $sortDirection)->orderBy('id', 'asc');
        }

        return $query;
    }

    protected function setPageVariables($library, $options)
    {
        $this->page['sortOptions'] = Library::$allowSortingOptions;
        $this->page['sortTypesOptions'] = (new Library())->getSortTypesOptions();
        $this->page['total_file_size_bites'] = $library->reduce(function ($carry, $item) {
            return $carry + ($item->file ? $item->file->file_size : 0);
        }, 0);
        $this->page['total_file_size'] = $this->page['total_file_size_bites'];
        $this->page['searchQuery'] = $options['search'];
        $this->page['currentType'] = $options['type'];
        $this->page['currentSort'] = $options['sort'];
    }

    /**
     * Handles the download action for all library items.
     *
     * This method aggregates all library items into a single ZIP file and triggers the download for the user.
     * It sets appropriate headers to initiate the download process.
     */


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

    public function hasLibrary()
    {
        return Library::exists();
    }
}
