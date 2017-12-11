<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lawer extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
    	'placeId', 
        'uniqueId', 
        'name',
        'phone',
        'email',
        'website',
        'streetAddress',
        'postCode',
        'addressLocality',
        'lat',
        'lon',
        'parsedwithgoogle',
        'googlename',
        'googleaddress',
        'googlephone',
        'googlewebsite'
    ];

}
