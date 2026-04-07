<?php namespace Pensoft\Library\Models;

use Model;

class LibraryType extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    public $timestamps = false;

    public $table = 'pensoft_library_types';

    public $rules = [
        'name' => 'required',
    ];

    public $hasMany = [
        'records' => [Library::class, 'key' => 'type'],
    ];
}
