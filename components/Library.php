<?php

namespace Pensoft\Library\Components;

use \Cms\Classes\ComponentBase;
use \Pensoft\Library\Models\Library as ModelsLibrary;

class Library extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Library Records',
            'description' => 'Displays a collection of libraries.'
        ];
    }

    public function records()
    {
        return ModelsLibrary::isVisible()->get();
    }
}
