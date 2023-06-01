<?php 

namespace Pensoft\Library\Classes;

use Pensoft\Library\Models\Library;
use \ZipArchive;

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
        $withFiles = function($item){
            return isset($item->file);
        }
        $records = $model->isVisible()->get()->filter($withFiles);

        $zip_file = tempnam(sys_get_temp_dir(), "publications");
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
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
}
