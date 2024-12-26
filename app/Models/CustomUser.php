<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;


class CustomUser extends Model
{
    protected $id;
    protected $username;

    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    public function getJWTIdentifier()
    {
        return $this->id; // The unique identifier of the user (e.g., user ID)
    }

    public function getJWTCustomClaims()
    {
        return []; // Custom claims (optional)
    }
}
