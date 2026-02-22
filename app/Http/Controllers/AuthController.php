<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        // Utilizar las políticas
        Gate::authorize('create', User::class);

        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        return response()->json($user, 201);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Verify the token and return the authenticated user's information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            $permissions = $user->getAllPermissions()->pluck('name');
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'full_name' => $user->name . ' ' . $user->surname,
                    'email' => $user->email,
                    'avatar' => $user->avatar ? env("APP_URL") . "/storage/" . $user->avatar : null,
                    'role' => [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                        'permissions' => $permissions,
                    ]
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token is invalid or expired',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    protected function respondWithToken($token)
    {
        $permissions = JWTAuth::user()->getAllPermissions()->pluck('name');
        return response()->json([
            'id' => JWTAuth::user()->id,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_at' => now()->addMinutes(JWTAuth::factory()->getTTL())->getPreciseTimestamp(3), // Timestamp absoluto en milisegundos
            "user" => [
                "full_name" => JWTAuth::user()->name . ' ' . JWTAuth::user()->surname,
                "email" => JWTAuth::user()->email,
                "avatar" => JWTAuth::user()->avatar ? env("APP_URL") . "/storage/" . JWTAuth::user()->avatar : NULL,
                "role" => [
                    "id" => JWTAuth::user()->role->id,
                    "name" => JWTAuth::user()->role->name,
                    "permissions" => $permissions,
                ],
            ]
        ]);
    }
}
