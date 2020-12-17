<?php namespace Pensoft\Library;


use System\Classes\PluginBase;
use Pensoft\Library\Components\Library;
use Pensoft\Library\Components\LibraryPage;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            Library::class => 'library',
            LibraryPage::class => 'LibraryPage',
        ];
    }

}
