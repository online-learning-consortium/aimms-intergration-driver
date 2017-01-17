<?php

namespace OLC\AIMSUserDriver\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $table = "roles";

    public $primaryKey = "id";

    public $timestamps = true;

    public $guarded = [
      
    ];

    public static $rules = [
        // create rules
    ];
}
