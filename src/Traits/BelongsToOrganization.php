<?php namespace OLC\AIMSUserDriver\Traits;

 use OLC\AIMSUserDriver\Repositories\OrganizationRepository;
 use OLC\AIMSUserDriver\Models\Relations\OneHttpRelation;
 use Illuminate\Database\Eloquent\Builder;
 use OLC\AIMSUserDriver\Models\DummyModel;
 use Cache;

 /*
    If there's something that needs acceptance testing it is probally this 
  */
 trait BelongsToOrganization
 {

 	public function organization()
 	{
 		$builder            = app(Builder::class);
 		$relation           = new OneHttpRelation($builder,$this);
 		$relation->otherKey = 'organization_id';
 		$relation->repository = app(OrganizationRepository::class);
 		$relation->table      = 'organizations';
 		return $relation;
 	}
}