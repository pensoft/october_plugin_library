<?php

namespace Pensoft\Library\Classes;

use Pensoft\Library\Models\Library;

class ZipFiles
{
    public $model;

    public function __construct(Library $model)
    {
        $this->model = $model;
    }

    public function downloadFiles($filename = null){
        if(is_null($filename)){
            $filename = 'file';
        }
        $model = $this->model;
        $records = $model->isVisible()->get()->filter(function($item){
            return isset($item->file);
        });

        $zip_file = tempnam(sys_get_temp_dir(), "publications");
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);


        foreach ($records as $item) {
            $file = $item->file->getLocalPath();
            $newFilename = 'publications/'.substr($file, strrpos($file, '/') + 1);
            $zip->addFile(
                $file,
                $newFilename
            );
        }


        $zip->close();
        return $zip_file;

    }

    /**
     * Build a zip file from a query or iterable list of records.
     * Accepts an Eloquent/October query builder (preferred) or any iterable of records.
     * Returns path to a temporary zip file ready to be streamed.
     * Uses chunking for queries to avoid high memory usage on large datasets.
     *
     * @param mixed $source Query builder or iterable of Library records
     * @return string Absolute path to the generated temporary zip file
     */
    public function downloadFilesFromQuery($source)
    {
        $zip_file = tempnam(sys_get_temp_dir(), "publications");
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $addRecords = function($records) use ($zip) {
            foreach ($records as $item) {
                if (!isset($item->file) || !$item->file) {
                    continue;
                }
                $file = $item->file->getLocalPath();
                if (!$file || !is_file($file)) {
                    continue;
                }
                $base = basename($file);
                $newFilename = 'publications/' . $base;
                // Ensure unique name inside the zip when duplicates exist
                if ($zip->locateName($newFilename) !== false) {
                    $newFilename = 'publications/' . $item->id . '_' . $base;
                }
                $zip->addFile($file, $newFilename);
            }
        };

        // If it's a builder with chunk support, process in chunks
        if (is_object($source) && method_exists($source, 'chunk')) {
            $source->chunk(200, function($chunk) use ($addRecords) {
                $addRecords($chunk);
            });
        } elseif (is_iterable($source)) {
            $addRecords($source);
        }

        $zip->close();
        return $zip_file;
    }
}
