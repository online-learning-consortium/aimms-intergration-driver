<?php

namespace OLC\AIMSUserDriver\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public $table = "organizations";

    public $primaryKey = "id";

    public $timestamps = true;

    public $guarded = [
      
    ];

    public static $rules = [
        // create rules
    ];
}
