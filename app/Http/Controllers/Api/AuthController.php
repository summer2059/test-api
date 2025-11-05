<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Permission\V1\Abilities;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Handle user login and return a personal access token.
     *
     * @group Authentication
     *
     * This endpoint allows a user to authenticate using their email and password.
     * On successful authentication, it returns a Sanctum API token with abilities.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password. Example: secret123
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Authenticated",
     *   "data": {
     *      "token": "1|abcdef1234567890..."
     *   }
     * }
     *
     * @response 401 {
     *   "status": "error",
     *   "message": "email or password is incorrect"
     * }
     *
     * @param \App\Http\Requests\Api\LoginUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('email or password is incorrect', 401);
        }

        $user = User::firstWhere('email', $request->email);

        return $this->ok('Authenticated', [
            'token' => $user->createToken(
                'API Token of ' . $user->name,
                Abilities::getAbilities($user),
                now()->addMonth()
            )->plainTextToken
        ]);
    }

    /**
     * Log out the authenticated user and revoke their current access token.
     *
     * @group Authentication
     *
     * This endpoint deletes the user's current token, effectively logging them out.
     *
     * Requires a valid Bearer token in the Authorization header.
     *
     * @header Authorization string required Bearer {token}
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "",
     *   "data": []
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('');
    }
}
