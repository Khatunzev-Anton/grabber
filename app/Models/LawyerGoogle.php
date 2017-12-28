<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LawyerGoogle extends Model
{
    //
    public $timestamps = false;

    protected $table = 'lawyersgoogle';

    protected $fillable = [
    	'placeid', 
        'uniqueid', 
        'name',
        'phone',
        'email',
        'website',
        'streetaddress',
        'rating',
        'reviews',
        'websitemetadescription',
        'websitemetakeywords',
        'websitepagescount',
        'websiteemail',
        'websitephone'
    ];
}
