<?php

namespace OLC\AIMSUserDriver\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use OLC\AIMSUserDriver\Repositories\OrganizationRepository;
use OLC\AIMSUserDriver\Repositories\UserRepository;

class User implements AuthenticatableContract
{
    /**
     * All of the user's attributes.
     *
     * @var array
     */
    protected $attributes;

    public $organizationPermissionKey;
    public $organization;

    /**
     * Create a new generic User object.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
        if (array_key_exists('first_name', $attributes) && array_key_exists('last_name', $attributes))
        {
            if (empty($attributes['first_name']) && empty($attributes['last_name']))
            {
                $this->attributes['name'] = 'EmptyName';
            }
            else
            {
                $this->attributes['name'] = $attributes['first_name'] . ' ' . $attributes['last_name'];
            }
        }
    }

    public function getUserAttributes()
    {
        return $this->attributes;
    }

    public function getAuthIdentifier()
    {
        return array_key_exists('id', $this->attributes) ? $this->attributes['id'] : null;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthPassword()
    {
        return $this->api_token;
    }

    public function getRememberToken()
    {
        return $this->attributes['remember_token'];
    }

    public function setRememberToken($value)
    {
        $this->attributes['remember_token'] = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public static function find($id)
    {
        return app(UserRepository::class)->find($id);
    }

    /**
     * Check if user has role
     *
     * @param  string  $role
     * @return boolean
     */
    public function hasRole($role)
    {
        switch ($role)
        {
        case 'admin':
            return $this->roles->contains('name', 'Administrator');
            break;
        case 'Institute Member':
            if (is_object($this->organization_membership) && ($this->organization_membership->membership_type_name == 'Institutional Membership') && $this->organization_membership->membership_type->active)
                {
                return true;
            }
            break;
        case 'Community Member':
            if (is_object($this->membership) && ($this->membership->membership_type_name == 'Community Membership') && $this->membership->membership_type->active)
                {
                return true;
            }
            break;
        default:
            return $this->roles->contains('name', $role);
            break;
        }
    }
    /**
     * Check if user a permission by a specific key.
     * @param  [type]  $key "name" column of permission ex: departments.{id}.moderator
     * @return boolean
     */
    public function hasPermission($permissionKey)
    {
        return $this->permissions->contains('name', $permissionKey);
    }

    public function getOrganizationKey()
    {
        if (!isset($this->organization))
        {
            return 'null';
        }
        $this->organization              = app(OrganizationRepository::class)->find($this->organization->id);
        $this->organizationPermissionKey = "organizations.{$this->organization->id}.admin";
        return $this->organizationPermissionKey;
    }

    public function canManageOrganization($adminRole = 'admin')
    {
        $orgKey = isset($this->organizationPermissionKey) ? $this->organizationPermissionKey : $this->getOrganizationKey();
        if (isset($this->organization) && ($this->hasPermission($orgKey) || $this->hasRole($adminRole)))
        {
            return true;
        }
        return false;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function toJson()
    {
        return json_encode($this->attributes);
    }

    /**
     * Dynamically access the user's attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    /**
     * Dynamically set an attribute on the user.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Dynamically check if a value is set on the user.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset a value on the user.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

}
