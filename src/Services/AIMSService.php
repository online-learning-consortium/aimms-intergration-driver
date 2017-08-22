<?php namespace OLC\AIMSUserDriver\Services;

use Drapor\Networking\Networking;
use GuzzleHttp\Exception\RequestException;

class AIMSService extends Networking
{

    public $baseUrl;
    public $token;
    public $orgPrefix;
    public $userPrefix;
    public $userPrefix2;

    public function __construct()
    {
        $this->token            = config('services.aims.token');
        $this->baseUrl          = config('services.aims.url');
        $this->orgPrefix        = config('services.aims.orgPrefix');
        $this->userPrefix       = config('services.aims.userPrefix');
        $this->userPrefix2      = config('services.aims.userPrefix2');
        $this->membershipPrefix = config('services.aims.membershipPrefix');
        $this->options['query'] = true;
        parent::__construct();
        $this->events                 = null;
        $this->request_headers        = $this->getDefaultHeaders();
        $this->request_headers['app'] = 'scorecard';
    }
    public function readResponse($data)
    {
        if (array_key_exists('body', $data))
        {
            if ($data['body'] == 'Not Found')
            {
                dd('failed');
            }
            return $data['body'];
        }
        else
        {
            dd($data);
        }
        return null;
    }

    public function jsonRequest($data)
    {
        $response                              = null;
        $this->request_body                    = $data;
        $this->method                          = 'post';
        $this->request_headers['content-type'] = 'application/json';
        try {
            $response = $this->createStreamRequest();
        }
        catch (RequestException $e)
        {
            if ($e->hasResponse())
            {
                $response = json_decode((String) $e->getResponse()->getBody(), true);
                if (!isset($response))
                {
                    $response = ['data' => ['errors' => 'unsure']];
                }
            }
        }
        return $response;
    }

    public function postMembership($data)
    {
        $this->url = $this->baseUrl . $this->membershipPrefix . "?api_token={$this->token}";
        return $this->jsonRequest($data);
    }

    public function getOrganization($id)
    {
        $endpoint               = $this->orgPrefix . "/" . $id;
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send(['api_token' => $this->token], $endpoint, 'get'));
    }

    public function organizationsBy($membershipType)
    {
        $endpoint               = $this->membershipPrefix . "/{$membershipType}";
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send(['api_token' => $this->token], $endpoint, 'get'));
    }

    public function register($data)
    {
        $this->url = $this->baseUrl . $this->userPrefix . "?api_token={$this->token}";
        return $this->jsonRequest($data);
    }

    public function login($user, $password)
    {
        $endpoint              = $this->userPrefix . '/login';
        $this->options['body'] = true;
        return $this->readResponse($this->send(['api_token' => $this->token, 'email' => $user, 'password' => $password], $endpoint, 'post'));
    }

    public function getUsersByRole($role)
    {
        $endpoint               = $this->userPrefix2 . "/$role/role";
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send(['api_token' => $this->token], $endpoint, 'get'));
    }

    public function getUserWhere($column, $value)
    {
        $endpoint               = $this->userPrefix2+"/?$column-like=$value";
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send(['api_token' => $this->token], $endpoint, 'get'));
    }

    public function searchUsers($value)
    {
        $endpoint               = $this->userPrefix2 . "/?_q=$value";
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send([], $endpoint, 'get'));
    }

    public function getUser($id)
    {
        $endpoint               = $this->userPrefix . "/{$id}";
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send(['api_token' => $this->token], $endpoint, 'get'));
    }
    public function usersBy(array $ids, $type = 'id')
    {
        $ids                    = implode('|', $ids);
        $endpoint               = $this->userPrefix2;
        $this->options['query'] = true;
        $this->options['body']  = false;
        return $this->readResponse($this->send([$type => $ids], $endpoint, 'get'));
    }
}
