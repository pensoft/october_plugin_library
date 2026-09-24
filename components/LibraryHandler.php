<?php

namespace Pensoft\Library\Components;

use Cms\Classes\Theme;
use \Cms\Classes\ComponentBase;
use Pensoft\Library\Classes\ZipFiles;
use Pensoft\Library\Models\Library;

/**
 * Library Handler Component
 *
 * Frontend-oriented component that exposes lightweight AJAX handlers.
 * It mirrors the filtering, sorting and search logic from the legacy
 * `LibraryPage` component but returns JSON so the frontend can render
 * the list without server-side templates.
 */
class LibraryHandler extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Library Handler',
            'description' => 'AJAX handler for Library filters/search/sort.'
        ];
    }

    public function defineProperties()
    {
        return [
            'milestones_filter' => [
                'title' => 'Enable and disable milestones filtration',
                'type' => 'checkbox',
                'default' => false,
            ],
            'features_filter' => [
                'title' => 'Enable and disable features filtration',
                'type' => 'checkbox',
                'default' => false,
            ],
            'technical_briefs_filter' => [
                'title' => 'Enable and disable technical briefs filtration',
                'type' => 'checkbox',
                'default' => false,
            ],
        ];
    }

    public function onRun()
    {
        $this->page['themeName'] = Theme::getActiveTheme()->getConfig()['name'];
        $this->page['sortOptions'] = Library::$allowSortingOptions;
        $this->page['sortTypesOptions'] = (new Library())->getSortTypesOptions();

        // Provide initial server-rendered list so the page has content without JS
        $options = $this->getRequestOptions();
        $query = $this->buildQuery($options);
        $records = $query->paginate($options['perPage'], $options['page']);
        $this->page['records'] = $records;
    }

    /**
     * Compute defaults and normalize request options.
     */
    protected function getRequestOptions(): array
    {
        $type = request()->get('type', '0');
        $defaultSort = ($type == '1' || $type == '4') ? 'title asc' : 'year desc';

        return request()->only(['page', 'perPage', 'search']) + [
            'page' => (string) request('page', '1'),
            'perPage' => (string) request('perPage', '15'),
            'type' => (string) $type,
            'sort' => request('sort', request('Filter.sort', $defaultSort)),
            'search' => request()->get('search'),
        ];
    }

    /**
     * Build the query with filters and sorting applied.
     */
    protected function buildQuery(array $options)
    {
        $query = Library::isVisible();

        if (!empty($options['search'])) {
            $query = $query->search($options['search']);
        }

        if ($options['type'] !== '' && $options['type'] !== null) {
            $query = $query->filterBy($options['type']);
        }

        if (!empty($options['sort'])) {
            [$sortField, $sortDirection] = explode(' ', $options['sort'], 2);
            $query = $query->sortBy($sortField, $sortDirection)->orderBy('id', 'asc');
        }

        return $query;
    }

    /**
     * AJAX: Return rendered partials like videos page (no HTML built in JS).
     */
    public function onFilter()
    {
        $options = [
            'page' => request('page', '1'),
            'perPage' => request('perPage', '15'),
            'type' => request('type', '0'),
            'sort' => request('sort', null) ?? request('Filter.sort'),
            'search' => request('search'),
        ];

        if (empty($options['sort'])) {
            $options['sort'] = ($options['type'] == '1' || $options['type'] == '4') ? 'title asc' : 'year desc';
        }

        $query = $this->buildQuery($options);
        $records = $query->paginate($options['perPage'], $options['page']);

        $totalSizeBytes = $records->reduce(function ($carry, $item) {
            return $carry + ($item->file ? $item->file->file_size : 0);
        }, 0);

        $html = '';
        $paginationHtml = '';
        try {
            $html = $this->controller->renderPartial('components/library-list', ['records' => $records]);
        } catch (\Throwable $e) {
            \Log::error('LibraryHandler::onFilter partial render failed: '.$e->getMessage());
            $html = '';
        }
        try {
            $paginationHtml = $records->render();
        } catch (\Throwable $e) {
            \Log::error('LibraryHandler::onFilter pagination render failed: '.$e->getMessage());
            $paginationHtml = '';
        }

        return [
            'html' => $html,
            'pagination' => $paginationHtml,
            'meta' => [
                'total_file_size_bites' => $totalSizeBytes,
                'total_file_size_mb' => $totalSizeBytes ? round($totalSizeBytes / 1024 / 1024, 2) : 0,
            ],
        ];
    }

    /**
     * Download ZIP with all visible files.
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
}