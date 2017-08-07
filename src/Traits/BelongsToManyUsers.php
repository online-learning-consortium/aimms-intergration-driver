<?php namespace OLC\AIMSUserDriver\Traits;

use Illuminate\Database\Eloquent\Builder;
use OLC\AIMSUserDriver\Models\Relations\ManyHttpRelations;

/*
If there's something that needs acceptance testing it is probally this
 */
trait BelongsToManyUsers
{
    public $btmUsersTable;

    public function users()
    {
        $builder = app(Builder::class);
        return new ManyHttpRelations($builder, $this);
    }

}
