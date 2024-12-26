<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $id;
    protected $username;

    public function __construct($id = null, $username = null)
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
