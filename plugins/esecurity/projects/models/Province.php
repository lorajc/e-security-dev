<?php namespace Esecurity\Projects\Models;

use Model;

/**
 * Province Model
 */
class Province extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'esecurity_projects_province';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['idPays'];

    public $belongsTo = [
        'country' => ['Esecurity\Projects\Models\Country']
    ];

    protected static $nameList = [];

    public static function getNameList($countryId)
    {
        if (isset(self::$nameList[$countryId])) {
            return self::$nameList[$countryId];
        }

        return self::$nameList[$countryId] = self::where('country_id', $countryId)
            ->orderBy('name_en', 'asc')
            ->lists('name_en', 'id');
    }

}