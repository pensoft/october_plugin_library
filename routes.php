<?php

use Illuminate\Support\Facades\Route;
use Pensoft\Library\Models\Library;
use Pensoft\Library\Classes\ZipFiles;
use Pensoft\Library\Classes\DownloadLink;

/*
|--------------------------------------------------------------------------
| Friendly single-file download route
|--------------------------------------------------------------------------
| /download/<file id>/<slug of the original file name>.<ext>, sent with the
| original file name. Allowed attachments: config download.models.
*/

Route::get(DownloadLink::prefix() . '/{id}/{name?}', function ($id, $name = null) {
    return DownloadLink::response($id, $name);
})->where(['id' => '[0-9]+', 'name' => '[^/]+'])->middleware('web');

/*
|--------------------------------------------------------------------------
| Frontend download route for Library ZIP
|--------------------------------------------------------------------------
| Streams a zip file of library items using normal HTTP download semantics.
| Accepts optional query params: type, sort, search to mirror UI filters.
*/

Route::get('resources/library/download', function () {
    $type = request('type', '0');
    $search = request('search');
    $sort = request('sort');

    if (empty($sort)) {
        $sort = ($type == '1' || $type == '4') ? 'title asc' : 'year desc';
    }

    $query = Library::isVisible();

    if (!empty($search)) {
        $query = $query->search($search);
    }

    if ($type !== '' && $type !== null) {
        $query = $query->filterBy($type);
    }

    if (!empty($sort)) {
        [$sortField, $sortDirection] = explode(' ', $sort, 2);
        $query = $query->sortBy($sortField, $sortDirection)->orderBy('id', 'asc');
    }

    $zipFile = (new ZipFiles(new Library()))->downloadFilesFromQuery($query);

    return response()->download($zipFile, 'publications.zip', [
        'Content-Type' => 'application/zip',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
    ])->deleteFileAfterSend(true);
});


