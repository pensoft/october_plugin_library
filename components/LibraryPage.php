<?php

namespace Pensoft\Library\Components;

use Cms\Classes\Theme;
use Backend\Facades\BackendAuth;
use Cms\Classes\ComponentBase;
use Pensoft\Library\Classes\ZipFiles;
use Pensoft\Library\Models\Library;

class LibraryPage extends ComponentBase
{
    public bool $loggedIn = false;

    public function onRun(): void
    {
        $this->addJs('assets/js/def.js');
        $this->prepareVars();

        $this->loggedIn = !empty(BackendAuth::getUser());
        $this->page['themeName'] = Theme::getActiveTheme()->getConfig()['name'];
    }

    public function defineProperties(): array
    {
        return [
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
                'default' => 'No records found',
            ],
            'redirect_to_download_page' => [
                'title' => 'Download file via file-download page',
                'type' => 'checkbox',
                'default' => false
            ],
            'milestones_filter' => [
                'title' => 'Enable milestones filtration',
                'type' => 'checkbox',
                'default' => false
            ],
            'features_filter' => [
                'title' => 'Enable features filtration',
                'type' => 'checkbox',
                'default' => false
            ],
            'technical_briefs_filter' => [
                'title' => 'Enable technical briefs filtration',
                'type' => 'checkbox',
                'default' => false
            ],
        ];
    }

    public function componentDetails(): array
    {
        return [
            'name' => 'LibraryPage',
            'description' => 'Displays a filterable collection of library records.'
        ];
    }

    protected function prepareVars(): void
    {
        $options = $this->getRequestOptions();
        $query = $this->applyFilters($options);

        $library = $query->paginate($options['perPage'], $options['page'])->appends(request()->query());

        $this->page['records'] = $library;
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

    protected function getRequestOptions(): array
    {
        $type = request()->get('type', '0');
        $defaultSort = ($type == '1' || $type == '4') ? 'title asc' : 'year desc';

        return request()->only(['page', 'perPage', 'search']) + [
            'page' => '1',
            'perPage' => '15',
            'type' => $type,
            'sort' => request('Filter.sort', $defaultSort),
            'search' => request()->get('search'),
        ];
    }

    protected function applyFilters(array $options)
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

    public function onDownloadAll(): void
    {
        $zip = (new ZipFiles(new Library))->downloadFiles();
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=publications.zip");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($zip);
        exit;
    }

    public function hasLibrary(): bool
    {
        return Library::exists();
    }

    public function onSearchRecords(): array
    {
        $sortType = post('sortType');
        $sortOrder = post('sortOrder');
        $this->page['records'] = $this->searchRecords($sortType, $sortOrder);
        return ['#recordsContainer' => $this->renderPartial('library_records')];
    }

    protected function searchRecords($sortType = 0, $sortOrder = 0)
    {
        $result = Library::isVisible();
        if ($sortType) {
            $result->filterBy("{$sortType}");
        }
        if ($sortOrder) {
            [$sortField, $sortDirection] = explode(' ', "{$sortOrder}", 2);
            $result = $result->sortBy($sortField, $sortDirection)->orderBy('id', 'asc');
        }
        return $result->get();
    }
}
