<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @var Request $request
     */
    private $request;

    /**
     * AuthController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Create a new token
     *
     * @param object $user
     * @return string
     */
    private function jwt($user): string
    {
        $payload = [
            'iss' => 'k8sctrl',
            'sub' => $user->id,
            'iat' => time(),
            'exp' => strtotime('+1 week')
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    /**
     * Check credentials and return generated JWT
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): JsonResponse
    {
        $this->validate($this->request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        // Find user
        $user = DB::table('user')
            ->where('username', '=', $this->request->input('username'))
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'Invalid username or password.'
            ], 401);
        }

        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $this->jwt($user)
            ], 200);
        }

        return response()->json([
            'error' => 'Invalid username or password.'
        ], 401);
    }

    /**
     * Returns a token with longer expiration time
     *
     * @return JsonResponse
     */
    public function renewToken(): JsonResponse
    {
        return response()->json([
            'token' => $this->jwt($this->request->user())
        ], 200);
    }
}