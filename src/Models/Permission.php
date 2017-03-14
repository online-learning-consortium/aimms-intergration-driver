<?php

namespace OLC\AIMSUserDriver\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $table = "permissions";

    public $primaryKey = "id";

    public $timestamps = true;

    public $guarded = [

    ];

    public static $rules = [
        // create rules
    ];

}
