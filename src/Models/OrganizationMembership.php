<?php

namespace OLC\AIMSUserDriver\Models;

use Illuminate\Database\Eloquent\Model as Model;

class OrganizationMembership extends Model
{
    const INSTITUTIONAL = 'institutional';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

     public $guarded = [
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];
    
        /**
     * @return boolean
     */
    public function expired()
    {
        return \Carbon\Carbon::now()->gte(\Carbon\Carbon::parse($this->end_at));
    }


}