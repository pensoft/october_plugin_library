<?php namespace Pensoft\Library\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Target audiences backend controller (Library > Target audiences)
 */
class Targets extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\ReorderController::class,
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['pensoft.library.access'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pensoft.Library', 'main-menu-item', 'side-menu-targets');
    }
}
