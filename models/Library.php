<?php namespace Pensoft\Library\Models;

use Carbon\Carbon;
use Model;
use Cms\Classes\Theme;
use BackendAuth;

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
    const TYPE_VIDEO = 7;
    const TYPE_PRESENTATION = 8;
    const TYPE_OTHER = 9;
    const TYPE_PLEDGES = 10;


    const SORT_TYPE_ALL = 0;
	const SORT_TYPE_DELIVERABLES = 1;
	const SORT_TYPE_RELEVANT_PUBLICATIONS = 2;
	const SORT_TYPE_PROJECT_PUBLICATIONS = 3;

    // Add  for revisions limit
    public $revisionableLimit = 200;

    // Add for revisions on particular field
    protected $revisionable = ["id", "title", "authors", "status", "year",];

    public static $allowSortingOptions = [
        'title asc' => 'Title (asc)',
        'title desc' => 'Title (desc)',
        'year desc' => 'Year (desc)',
        'year asc' => 'Year (asc)',
    ];

    public static $allowSortTypesOptions = [
        self::SORT_TYPE_ALL => "All Documents",
		self::SORT_TYPE_DELIVERABLES => 'Deliverables',
		self::SORT_TYPE_RELEVANT_PUBLICATIONS => 'Relevant Publications',
		self::SORT_TYPE_PROJECT_PUBLICATIONS =>  'Publications',
    ];

    public function getSortTypesOptions(){
        $activeTheme = Theme::getActiveTheme();
        $theme = $activeTheme->getConfig();
        return
        [
            self::SORT_TYPE_ALL => "All Documents",
            self::SORT_TYPE_DELIVERABLES => 'Deliverables',
            self::SORT_TYPE_RELEVANT_PUBLICATIONS => 'Relevant Publications',
            self::SORT_TYPE_PROJECT_PUBLICATIONS =>  strtoupper($theme['name']).' Publications',
        ];
    }

    public function scopeThemeName(){
        $activeTheme = Theme::getActiveTheme();
        $theme = $activeTheme->getConfig();
        return strtoupper($theme['name']).' Publications';
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

    // Add  below relationship with Revision model
    public $morphMany = [
        'revision_history' => ['System\Models\Revision', 'name' => 'revisionable']
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
            case self::TYPE_VIDEO:
                return 'Video';
                break;
            case self::TYPE_PRESENTATION:
                return 'Presentation';
                break;
            case self::TYPE_OTHER:
                return 'Other';
                break;
            case self::TYPE_PLEDGES:
                return 'Pledges';
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

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Add below function use for get current user details
    public function diff()
    {
        $history = $this->revision_history;
    }

    public function getRevisionableUser()
    {
        return BackendAuth::getUser()->id;
    }

    public function scopeDefaultSort()
    {
        $options = request()->only(['type']);
        return (!empty($options['type']) && $options['type'] == 1) ? 'title asc' : 'year desc';
    }

    /**
     * Scope to sort records
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $field, $direction)
    {
        if ($field === 'title') {
            return $query->fromSub(function ($query) use ($direction) {
                $query->from('pensoft_library_records')
                    ->selectRaw("*,
                                substring(title, '^([^0-9]*)') as title_start,
                                regexp_split_to_array(substring(title, '(\d+(\.\d+)?)'), '\.') as title_numbers");
            }, 'subquery')
                ->orderByRaw("title_start " . $direction)
                ->orderByRaw("cast(title_numbers[1] as integer) " . $direction)
                ->orderByRaw("CASE WHEN array_length(title_numbers, 1) > 1 THEN cast(title_numbers[2] as integer) END " . $direction);
        } else {
            return $query->orderBy($field, $direction);
        }
    }

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


}
