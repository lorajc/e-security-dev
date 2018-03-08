<?php namespace Esecurity\Projects\Models;

use Model;

/**
 * OrderItem Model
 */
class OrderItem extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'esecurity_projects_order_items';

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
        'order' => 'Esecurity\Projects\Models\Order',
        'product' => 'Esecurity\Projects\Models\Product',
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
