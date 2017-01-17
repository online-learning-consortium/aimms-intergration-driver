<?php

namespace OLC\AIMSUserDriver\Models;

use Illuminate\Database\Eloquent\Model as Model;

class Membership extends Model
{

    public $timestamps = false;

     public $guarded = [
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'membership_type_id',
        'parent_membership_id',
        'org_based',
    ];


    /**
     * @return boolean
     */
    public function expired()
    {
        return \Carbon\Carbon::now()->gte(\Carbon\Carbon::parse($this->end_at));
    }

}