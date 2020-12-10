<?php

namespace Pensoft\Library\Components;

use \Cms\Classes\ComponentBase;

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
        return \Pensoft\Library\Models\Library::isVisible()->get();
    }
}
