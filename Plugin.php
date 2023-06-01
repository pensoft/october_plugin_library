<?php 
namespace Pensoft\Library;

use System\Classes\PluginBase;
use Pensoft\Library\Components\Library;
use Pensoft\Library\Components\LibraryPage;
use SaurabhDhariwal\Revisionhistory\Classes\Diff;
use System\Models\Revision;

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
    public function registerComponents()
    {
        return [
            Library::class => 'library',
            LibraryPage::class => 'LibraryPage',
        ];
    }

}
