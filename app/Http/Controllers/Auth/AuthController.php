<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomUser;
use App\Models\DealarInvoiceDetails;
use App\Models\User;
use App\Models\YSparkLogin;
use App\Models\YSparkSmsLog;
use App\Services\JWTCustomSubject;
use App\Traits\CommonTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\jwt\JWT;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    use CommonTrait;
    public function login(Request $request)
    {

        $configCredentials = config('credentials');

        $inputUsername =  $request->header('username');
        $inputPassword = $request->header('Password');

        if ($inputUsername === $configCredentials['username'] && $inputPassword === $configCredentials['password']) {
            // Create a JWTSubject instance
            $customUser = new JWTCustomSubject(1, $inputUsername);

            // Generate token
            $token = JWTAuth::fromUser($customUser);

            return response()->json([
                'success' => true,
                'token' => $token,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }
    public function generateToken($user)
    {
        $payload = [
            'iat' => time(),
            'iss' => $_SERVER['SERVER_NAME'],
            'exp' => time() + 12 * 30 * (24 * 60 * 60),// 1 Month
            'EmpCode' => $user['EmpCode'],
            'staffCode' => $user['staffCode'],
            'Business' => $user['Business'],
            'Type' => $user['type'],
        ];
        try {
            $privateKey = env("JWT_SECRET", "t3d0qNv8858l6spwApKED6yxpcGF45XTUqfwu3JHIurcIKMZ1du5pNiJBkoDQ79a");
            $token = JWT::encode($payload, $privateKey);
            return $token;
        } catch (\Exception $ex) {
            $token = false;
        }
        return $token;

    }
    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return response()->json(['token' => $token]);
    }
}
