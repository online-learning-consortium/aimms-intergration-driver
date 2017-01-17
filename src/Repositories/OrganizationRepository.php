<?php namespace OLC\AIMSUserDriver\Repositories;

use Drapor\Networking\Networking;
use OLC\AIMSUserDriver\Models\User;
use OLC\AIMSUserDriver\Models\Membership;
use OLC\AIMSUserDriver\Models\Organization;
use OLC\AIMSUserDriver\Models\OrganizationMembership;
use OLC\AIMSUserDriver\Models\MembershipType;
use OLC\AIMSUserDriver\Services\AIMSService;

class OrganizationRepository
{
	protected $service;

	public function __construct(AIMSService $AIMSService)
	{
		$this->service  = $AIMSService;
	}

	public function map($organizationResponse)
	{
		if($organizationResponse != null)
		{
			$organization = new Organization($organizationResponse);
		}
		return $organization;
	}

	public function lists($type)
	{
		return $this->service->organizationsBy($type);
	}

	public function find($id)
	{
		return $this->map($this->service->getOrganization($id));
	}


}