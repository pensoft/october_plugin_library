<?php namespace Pensoft\Library;


use System\Classes\PluginBase;
use Pensoft\Library\Components\Library;
use Pensoft\Library\Components\LibraryPage;
use Pensoft\Library\Components\LibraryHandler;

class Plugin extends PluginBase
{
    public function boot(): void {}
    public function registerComponents(): array
    {
        return [
            Library::class => 'library',
            LibraryPage::class => 'LibraryPage',
            LibraryHandler::class => 'LibraryHandler',
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'pensoft.library.access' => [
                'tab' => 'Library',
                'label' => 'Manage library'
            ],
        ];
    }

    public function registerNavigation(): array
    {
        return [
            'main-menu-item' => [
                'label'       => 'Library',
                'url'         => \Backend::url('pensoft/library/library'),
                'icon'        => 'icon-book',
                'permissions' => ['pensoft.library.*'],
                'sideMenu' => [
                    'library' => [
                        'label' => 'Records',
                        'icon'  => 'icon-book',
                        'url'   => \Backend::url('pensoft/library/library'),
                        'permissions' => ['pensoft.library.*'],
                    ],
                    'library-types' => [
                        'label' => 'Types',
                        'icon'  => 'icon-tags',
                        'url'   => \Backend::url('pensoft/library/librarytypes'),
                        'permissions' => ['pensoft.library.*'],
                    ],
                    'library-statuses' => [
                        'label' => 'Statuses',
                        'icon'  => 'icon-flag',
                        'url'   => \Backend::url('pensoft/library/librarystatuses'),
                        'permissions' => ['pensoft.library.*'],
                    ],
                ],
            ],
        ];
    }

}