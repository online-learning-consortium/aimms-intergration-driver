<?php namespace OLC\AIMSUserDriver\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use OLC\AIMSUserDriver\Repositories\UserRepository;

class AIMSAuthProvider implements UserProvider
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function retrieveById($identifier)
    {
        $user = $this->userRepository->find($identifier);
        return $user;
    }

    public function retrieveByToken($identifier, $token)
    {
        $user = $this->userRepository->find($identifier);
        return $user;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->token = $token;
        return $user;
    }
    //This is the array passed to the login area.
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials))
        {
            return;
        }

        if (array_key_exists('password', $credentials) && array_key_exists('email', $credentials))
        {
            $user = $this->userRepository->login($credentials['email'], $credentials['password']);
            return $user;
        }
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return empty($user->errors);
    }

    public function __toString()
    {
        return 'aimms';
    }
}
