<?php namespace OLC\AIMSUserDriver\Repositories;

use OLC\AIMSUserDriver\Models\Membership;
use OLC\AIMSUserDriver\Models\MembershipType;
use OLC\AIMSUserDriver\Models\Organization;
use OLC\AIMSUserDriver\Models\OrganizationMembership;
use OLC\AIMSUserDriver\Models\Permission;
use OLC\AIMSUserDriver\Models\Role;
use OLC\AIMSUserDriver\Models\User;
use OLC\AIMSUserDriver\Services\AIMSService;

class UserRepository
{
    protected $service;

    public function __construct(AIMSService $AIMSService)
    {
        $this->service = $AIMSService;
    }

    public function map($userResponse)
    {
        if (!$userResponse || $userResponse == 'No Response Received.' || array_key_exists('message', $userResponse) && $userResponse['message'] == 'No Response Received.')
        {
            return;
        }
        $user        = new User($userResponse);
        $user->roles = collect();
        if (array_key_exists('roles', $userResponse))
        {
            foreach ($userResponse['roles'] as $role)
            {
                $user->roles->push(new Role($role));
            }
        }
        if (array_key_exists('permissions', $userResponse))
        {
            $user->permissions = collect();
            foreach ($userResponse['permissions'] as $permission)
            {
                $user->permissions->push(new Permission($permission));
            }
        }
        if (array_key_exists('membership', $userResponse) && $userResponse['membership'] != null)
        {
            $membership       = $userResponse['membership'];
            $user->membership = new Membership($membership);
            if (array_key_exists('membership_type', $membership))
            {
                $user->membership->membership_type = new MembershipType($userResponse['membership']['membership_type']);
            }
        }
        if (array_key_exists('organization_membership', $userResponse) && $userResponse['organization_membership'] != null)
        {
            $organizationMembership        = $userResponse['organization_membership'];
            $user->organization_membership = new OrganizationMembership($organizationMembership);
            if (array_key_exists('membership_type', $organizationMembership))
            {
                $user->organization_membership->membership_type = new MembershipType($userResponse['organization_membership']['membership_type']);
            }
        }
        if (array_key_exists('organization', $userResponse) && $userResponse['organization'] != null)
        {
            $user->organization = new Organization($userResponse['organization']);
        }
        $user->remember_token = null;
        return $user;
    }

    public function login($user, $password)
    {
        $response = $this->service->login($user, $password);
        if (array_key_exists('errors', $response))
        {
            //This might seem odd, but Auth will expect a User Object to validate credentials on. We're going to make Auth work by passing in this empty object.
            $user = new User($response);
            return $user;
        }
        return $this->map($response);
    }

    public function whereRole($role)
    {
        return collect(array_map([$this, 'map'], $this->service->getUsersByRole($role)));
    }

    public function whereIds(array $ids)
    {
        return collect(array_map([$this, 'map'], $this->service->usersBy($ids)));
    }

    public function whereEmails(array $emails)
    {
        return collect(array_map([$this, 'map'], $this->service->usersBy($emails, 'email')));
    }

    public function register($data)
    {
        $response = $this->service->register($data);
        if (array_key_exists('errors', $response))
        {
            return $response;
        }
        else
        {
            return $this->map($response);
        }
    }

    public function searchUsers($value)
    {
        return collect(array_map([$this, 'map'], $this->service->searchUsers($value)));
    }

    public function find($id)
    {
        $userResponse = $this->service->getUser($id);
        return $this->map($userResponse);
    }

}