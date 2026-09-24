<?php namespace Pensoft\Library;


use System\Classes\PluginBase;
use Pensoft\Library\Components\Library;
use Pensoft\Library\Components\LibraryPage;
use Pensoft\Library\Components\LibraryHandler;
use Pensoft\Library\Classes\DownloadLink;
use SaurabhDhariwal\Revisionhistory\Classes\Diff as Diff;
use System\Models\Revision as Revision;

class Plugin extends PluginBase
{
    public function boot(){
        /* Extetions for revision */
        Revision::extend(function($model){
            /* Revison can access to the login user */
            $model->belongsTo['user'] = ['Backend\Models\User'];

            /* Revision can use diff function */
            $model->addDynamicMethod('getDiff', function() use ($model){
                return Diff::toHTML(Diff::compare($model->old_value, $model->new_value));
            });
        });
    }
    public function registerMarkupTags()
    {
        return [
            'functions' => [
                // Friendly download link + original file name for an attached file
                'download_url' => [DownloadLink::class, 'url'],
                'download_name' => [DownloadLink::class, 'name'],
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            Library::class => 'library',
            LibraryPage::class => 'LibraryPage',
            LibraryHandler::class => 'LibraryHandler',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Library',
                'description' => 'Frontend library options (Target audience filter)',
                'category'    => 'Library',
                'icon'        => 'icon-book',
                'class'       => \Pensoft\Library\Models\Settings::class,
                'order'       => 500,
                'keywords'    => 'library target audience filter',
                'permissions' => ['pensoft.library.access'],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'pensoft.library.access' => [
                'tab' => 'Library',
                'label' => 'Manage library'
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'main-menu-item' => [
                'label'       => 'Library',
                'url'         => \Backend::url('pensoft/library/library'),
                'icon'        => 'icon-book',
                'permissions' => ['pensoft.library.*'],
                'sideMenu'    => [
                    'side-menu-records' => [
                        'label'       => 'Records',
                        'url'         => \Backend::url('pensoft/library/library'),
                        'icon'        => 'icon-book',
                        'permissions' => ['pensoft.library.*'],
                    ],
                    'side-menu-targets' => [
                        'label'       => 'Target audiences',
                        'url'         => \Backend::url('pensoft/library/targets'),
                        'icon'        => 'icon-users',
                        'permissions' => ['pensoft.library.*'],
                    ],
                ],
            ],
        ];
    }

}
