<?php namespace OLC\AIMSUserDriver\Traits;

 use OLC\AIMSUserDriver\Repositories\UserRepository;
 use OLC\AIMSUserDriver\Models\Relations\OneHttpRelation;
 use Illuminate\Database\Eloquent\Builder;
 use OLC\AIMSUserDriver\Models\DummyModel;
 use Cache;

 /*
    If there's something that needs acceptance testing it is probally this 
  */
 trait BelongsToUser
 {

 	public function user()
 	{
 		$builder  =  app(Builder::class);
 		$relation = new OneHttpRelation($builder,$this);
 		$relation->otherKey = 'user_id';
 		$relation->repository = app(UserRepository::class);
 		$relation->table      = 'users';
 		return $relation;
 	}
}