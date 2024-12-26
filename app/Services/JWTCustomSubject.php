<?php

namespace App\Services;

use Tymon\JWTAuth\Contracts\JWTSubject;

class JWTCustomSubject implements JWTSubject
{
    protected $id;
    protected $username;

    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->id;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'username' => $this->username,
        ];
    }
}
