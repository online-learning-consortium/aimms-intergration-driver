<?php
namespace OLC\AIMSUserDriver\Guards;

use OLC\AIMSUserDriver\Services\AIMSService;
use OLC\AIMSUserDriver\Providers\AIMSAuthProvider;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\SessionGuard;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Session\Session;
use Cookie;

class AIMSGaurd extends SessionGuard
{

    public function __construct($name = null,AIMSAuthProvider $aimsAuthProvider, Session $session,Request $request = null)
    {
        if($name == null)
        {
            $name = 'aims';
        }
        parent::__construct($name,$aimsAuthProvider,$session,$request);
    }

    public function signin($credentials)
    {
        $this->fireAttemptEvent($credentials, false, true);
        $user                = $this->provider->retrieveByCredentials($credentials);
        $this->lastAttempted = $user;

        if($user !== null)
        {
            $this->login($user, false);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function signout()
    {
        $this->clearUserDataFromStorage();

        if(isset($this->events))
        {
            $this->events->fire('auth.logout', [$this->user()]);
        }

        $this->user = null;
        $this->loggedOut = true;
    }

     /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut)
        {
            return;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $id = Cookie::get(config('auth.cookie_name'));


        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        $user = null;

        if (! is_null($id)) {
            $user = $this->provider->retrieveById($id);
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $recaller = $this->recaller();

        if (is_null($user) && ! is_null($recaller)) {
            $user = $this->getUserByRecaller($recaller);

            if ($user) {
                $this->updateSession($user->getAuthIdentifier());

                $this->fireLoginEvent($user, true);
            }
        }

        return $this->user = $user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->signin($credentials);
    }

}
