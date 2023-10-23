<?php namespace Pensoft\Library\Models;

use Carbon\Carbon;
use Model;
use Cms\Classes\Theme;
<<<<<<< Updated upstream
=======
use BackendAuth;
use Validator;
>>>>>>> Stashed changes

/**
 * Model
 */
class Library extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    const STATUS_PUBLISHED = 1;
    const STATUS_INPRESS = 2;
    const STATUS_INPREPARATION = 3;
    const STATUS_OTHER = 4;

    const DERIVED_YES = 1;
    const DERIVED_NO = 2;

    const TYPE_JOURNAL_PAPER = 1;
    const TYPE_PROCEEDINGS_PAPER = 2;
    const TYPE_BOOK_CHAPTER = 3;
    const TYPE_BOOK = 4;
    const TYPE_DELIVERABLE = 5;
    const TYPE_REPORT = 6;

	const SORT_TYPE_DELIVERABLES = 1;
	const SORT_TYPE_RELEVANT_PUBLICATIONS = 2;
	const SORT_TYPE_PROJECT_PUBLICATIONS = 3;

    public static $allowSortingOptions = [
        'year asc' => 'Year (asc)',
        'year desc' => 'Year (desc)',
    ];

    /**
     * @var array Translatable fields
     */
    public $translatable = [
        'title',
        'type',
        'authors',
        'journal_title',
        'proceedings_title',
        'monograph_title',
        'project_title',
        'volume_issue',
        'publisher',
        'place',
        'city',
        'pages',
        'doi'
    ];
    
    public static $allowSortTypesOptions = [
		self::SORT_TYPE_DELIVERABLES => 'Deliverables',
		self::SORT_TYPE_RELEVANT_PUBLICATIONS => 'Relevant Publications',
		self::SORT_TYPE_PROJECT_PUBLICATIONS =>  'Publications',
    ];

    public function getSortTypesOptions(){
		$activeTheme = Theme::getActiveTheme();
		$theme = $activeTheme->getConfig();
        return
        [
            self::SORT_TYPE_DELIVERABLES => 'Deliverables',
            self::SORT_TYPE_RELEVANT_PUBLICATIONS => 'Relevant Publications',
            self::SORT_TYPE_PROJECT_PUBLICATIONS =>  strtoupper($theme['name']).' Publications',
        ];
    }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pensoft_library_records';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $attachOne = [
        'file' => 'System\Models\File',
		'preview' => 'System\Models\File',
    ];
    public $appends = [
		'status_attr',
		'derived_attr',
		'year_attr',
		'type_attr',
		'date_attr',
	];

    public function getDueDateAttribute($value)
    {
        return (new Carbon($value))->englishMonth;
    }

	public function getYearAttrAttribute()
	{
		return (new Carbon($this->year))->year;
	}



	public function getTypeAttrAttribute()
	{
		switch ($this->type){
			case self::TYPE_JOURNAL_PAPER:
				return 'Journal paper';
				break;
			case self::TYPE_PROCEEDINGS_PAPER:
				return 'Proceedings paper';
				break;
			case self::TYPE_BOOK_CHAPTER:
				return 'Book chapter';
				break;
			case self::TYPE_BOOK:
				return 'Book';
				break;
			case self::TYPE_DELIVERABLE:
			default:
				return 'Deliverable';
				break;
			case self::TYPE_REPORT:
				return 'Report';
				break;
		}
	}

    public function getDateAttrAttribute()
    {
        return (new Carbon($this->year))->day . ' ' . (new Carbon($this->year))->englishMonth .' '. (new Carbon($this->year))->year;
    }

    public function getStatusAttrAttribute()
    {
        switch((int) $this->status){
            case self::STATUS_PUBLISHED: return 'Published';
            case self::STATUS_INPRESS: return 'In Press';
            case self::STATUS_INPREPARATION: return 'In Preparation';
            case self::STATUS_OTHER: return 'Other';
        }
    }

    public function getDerivedAttrAttribute($value)
    {
        switch((int) $this->derived){
            case self::DERIVED_YES: return 'Yes';
            case self::DERIVED_NO: return 'No';
        }
    }

    public function getIsFileAttribute()
    {
        return isset($this->file);
    }

    public function scopeIsFile($query)
    {
        $query->has('file');
    }

    public function scopeIsVisible($query)
    {
        $query->where('is_visible', true);
    }

    public function scopeOfType($query, $type){
        return $query->where('type', $type);
    }

    public function scopeListFrontEnd($query, $options = []){
        extract(
            array_merge([
                'sort' => 'created_at desc',
                'type' => 0,
            ], $options)
        );

        switch ($type){
			case self::SORT_TYPE_DELIVERABLES:
				$query->ofType(self::TYPE_DELIVERABLE);
				break;
			case self::SORT_TYPE_RELEVANT_PUBLICATIONS:
                $query->ofType(self::TYPE_JOURNAL_PAPER)->where('derived', self::DERIVED_NO);
				break;
			case self::SORT_TYPE_PROJECT_PUBLICATIONS:
				$query->ofType(self::TYPE_JOURNAL_PAPER)->where('derived', self::DERIVED_YES);
				break;
		}

        if(in_array($sort, array_keys(self::$allowSortingOptions))){
            $parts = explode(' ', $sort);
            list($sortField, $sortDirection) = $parts;
            $query->orderBy($sortField, $sortDirection);
        }
    }
<<<<<<< Updated upstream
=======

    /**
     * Scope to filter records by type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByType($query, $type)
    {
        switch ($type) {
            case self::SORT_TYPE_DELIVERABLES:
                return $query->ofType(self::TYPE_DELIVERABLE);
            case self::SORT_TYPE_MILESTONES:
                return $query->ofType(self::TYPE_MILESTONE);
            case self::SORT_TYPE_RELEVANT_PUBLICATIONS:
                return $query->where('type', '!=', self::TYPE_DELIVERABLE)
                    ->where('derived', self::DERIVED_NO);
            case self::SORT_TYPE_PROJECT_PUBLICATIONS:
                return $query->where('type', '!=', self::TYPE_DELIVERABLE)
                    ->where('derived', self::DERIVED_YES);
            case "0":
                return $query;
        }

    }

    /**
     * Scope to search in records
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $searchTerm)
    {
        if (!$searchTerm) {
            return $query;
        }
        return $query->where(function ($query) use ($searchTerm) {
            $query->where('title', 'iLIKE', '%' . $searchTerm . '%')
                ->orwhere('authors', 'iLIKE', '%' . $searchTerm . '%')
                ->orwhere('journal_title', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('proceedings_title', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('monograph_title', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('deliverable_title', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('project_title', 'iLIKE', '%' . $searchTerm . '%')
                ->orwhere('publisher', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('place', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('city', 'iLIKE', '%' . $searchTerm . '%')
                ->orWhere('doi', 'iLIKE', '%' . $searchTerm . '%');
        });
    }

    /**
     * Add translation support to this model, if available.
     *
     * @return void
     */
    public static function boot()
    {
        Validator::extend(
            'json',
            function ($attribute, $value, $parameters) {
                json_decode($value);

                return json_last_error() == JSON_ERROR_NONE;
            }
        );

        // Call default functionality (required)
        parent::boot();

        // Check the translate plugin is installed
        if (!class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            return;
        }

        // Extend the constructor of the model
        self::extend(
            function ($model) {

                // Implement the translatable behavior
                $model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';
            }
        );
    }
>>>>>>> Stashed changes
}
