<?php
namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Services\CustomUser;

class CustomUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return new CustomUser($identifier, config('auth_credentials.username'));
    }

    public function retrieveByToken($identifier, $token)
    {
        // Not applicable for this custom setup
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Not applicable for this custom setup
    }

    public function retrieveByCredentials(array $credentials)
    {
        $storedUsername = config('auth_credentials.username');
        $storedPassword = config('auth_credentials.password');

        if (
            $credentials['username'] === $storedUsername &&
            $credentials['password'] === $storedPassword
        ) {
            return new CustomUser(1, $storedUsername);
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}
