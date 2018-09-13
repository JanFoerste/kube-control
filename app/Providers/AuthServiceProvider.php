<?php

namespace App\Providers;

use Firebase\JWT\JWT;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void|GenericUser
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function (Request $request) {

            if ($request->header('authorization')) {

                [$prefix, $token] = explode(' ', $request->header('authorization'));

                try {
                    $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
                    $user = DB::table('user')
                        ->find($credentials->sub);

                    if ($user) {
                        $this->defineGates();

                        return new GenericUser((array)$user);
                    }
                } catch (\Exception $e) {
                    return null;
                }
            }

            return null;
        });
    }

    /**
     * Define the authorization gates
     */
    private function defineGates(): void
    {
        Gate::define('edit-user', function ($user, $userToEdit) {
           return $user->id === $userToEdit->id || $user->role === 'admin';
        });
    }
}
