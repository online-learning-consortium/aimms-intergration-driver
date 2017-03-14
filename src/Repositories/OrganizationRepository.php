<?php namespace OLC\AIMSUserDriver\Repositories;

use OLC\AIMSUserDriver\Models\Organization;
use OLC\AIMSUserDriver\Services\AIMSService;

class OrganizationRepository
{
    protected $service;

    public function __construct(AIMSService $AIMSService)
    {
        $this->service = $AIMSService;
    }

    public function map($organizationResponse)
    {
        if ($organizationResponse != null)
        {
            $organization = new Organization($organizationResponse);
            if (isset($organization->children))
            {
                $children               = collect($organization->children);
                $organization->children = $children;
            }
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