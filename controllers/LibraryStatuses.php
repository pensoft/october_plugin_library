<?php namespace Pensoft\Library\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class LibraryStatuses extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ReorderController::class,
    ];

    public string $listConfig = 'config_list.yaml';
    public string $formConfig = 'config_form.yaml';
    public string $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pensoft.Library', 'main-menu-item', 'library-statuses');
    }
}
