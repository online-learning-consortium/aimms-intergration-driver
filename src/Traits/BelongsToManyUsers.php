<?php namespace OLC\AIMSUserDriver\Traits;

 use OLC\AIMSUserDriver\Repositories\UserRepository;
 use OLC\AIMSUserDriver\Models\Relations\ManyHttpRelations;
 use Illuminate\Database\Eloquent\Builder;
 use OLC\AIMSUserDriver\Models\DummyModel;
 use DB;

 /*
    If there's something that needs acceptance testing it is probally this 
  */
 trait BelongsToManyUsers
 {
 	public $btmUsersTable;

 	public function users()
 	{
 		$builder    =  app(Builder::class);
 		return new ManyHttpRelations($builder,$this);
 	}
}