<?php
namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class CustomUser implements Authenticatable
{
    private $id;
    private $username;

    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Get the password for the user (not used in this setup).
     */
    public function getAuthPassword()
    {
        return null;
    }

    /**
     * Get the "remember me" token (not used in this setup).
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the "remember me" token (not used in this setup).
     */
    public function setRememberToken($value)
    {
        // Not applicable
    }

    /**
     * Get the name of the "remember me" token (not used in this setup).
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }
}
