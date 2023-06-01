<?php 

namespace Pensoft\Library;

if(!function_exists('exists')){
    function exists($value){
        if(gettype($value) === "string" && $value === '0'){
            return true;
        }
        return !empty($value);
    }
}

if(!function_exists('exists_with') 
    && function_exists('exists')){
    function exists_with($value, $rules, $strict = false){
        return exists($value) && in_array($value, $rules, $strict);
    }
}

if(!function_exists('human_filesize')){
    function human_filesize($bytes, $dec = 2): string {
    
        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0) $dec = 0;

        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);
    }
}