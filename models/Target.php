<?php namespace Pensoft\Library\Models;

use Model;

/**
 * Target audience of a library record (e.g. Academics, Researchers, Policy makers, Technical experts).
 * Managed in the backend under Library > Target audiences.
 */
class Target extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    use \October\Rain\Database\Traits\Sluggable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pensoft_library_targets';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'slug'];

    /**
     * @var array Generate slugs for these attributes.
     */
    protected $slugs = ['slug' => 'name'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'unique:pensoft_library_targets',
    ];

    public $hasMany = [
        'records' => [Library::class, 'key' => 'target_id'],
    ];
}
