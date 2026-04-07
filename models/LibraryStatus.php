<?php namespace Pensoft\Library\Models;

use Model;

class LibraryStatus extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    public $timestamps = false;

    public $table = 'pensoft_library_statuses';

    public $rules = [
        'name' => 'required',
    ];

    public $hasMany = [
        'records' => [Library::class, 'key' => 'status'],
    ];
}
