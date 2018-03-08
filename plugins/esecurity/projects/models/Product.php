<?php namespace Esecurity\Projects\Models;

use Model;

/**
 * Product Model
 */
class Product extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'esecurity_projects_products';

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
    public $hasMany = [];
    public $belongsTo = [
        'category' => ['Esecurity\Projects\Models\Category'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        'images' => 'System\Models\File'
    ];

    public function getCategoryOptions()
    {
        return Category::getNameList();
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

    public function getGroupSlugAttribute()
    {
        if ($this->group == '1') {
            return "residential";
        } else {
            return "business";
        }
    }
}
