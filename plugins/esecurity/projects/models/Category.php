<?php namespace Esecurity\Projects\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'esecurity_projects_categories';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'products' => ['Esecurity\Projects\Models\Product'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];


    protected static $nameList = null;

    public static function getNameList()
    {
        if (self::$nameList) {
            return self::$nameList;
        }

        return self::$nameList = self::orderBy('name','asc')
            ->lists('name', 'id');
    }

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true);
    }

    public function getGroupOptions()
    {
        return ['1' => 'Residential','2' => 'Business' ];
    }

    public function getGroupNameAttribute()
    {
        if ($this->group == '1') {
            return "Residential";
        } else {
            return "Business";
        }
    }
}
