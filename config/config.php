<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Friendly download links
    |--------------------------------------------------------------------------
    | /<prefix>/<file id>/<slug of the original file name>.<ext>
    | (Pensoft\Library\Classes\DownloadLink, Twig download_url() / download_name())
    |
    | models: model class => attachment fields whose files may be served.
    | Override per site in config/pensoft/library/config.php — the override replaces
    | the whole `download` key, so repeat the Library entry there.
    */

    'download' => [
        'prefix' => 'download',
        'models' => [
            \Pensoft\Library\Models\Library::class => ['file'],
        ],
    ],

];
