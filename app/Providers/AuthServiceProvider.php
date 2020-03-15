<?php

namespace App\Providers;

use App\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function (Request $request) {
            if ($request->has('api_token')) {
                try {
                    $jwt = JWT::decode($request->input('api_token'), env('JWT_SECRET'), ['HS256']);
                } catch (ExpiredException $expiredException) {
                    return response()->json([
                        'meta' => [
                            'code' => 400,
                            'message' => 'Bad Request'
                        ], 'data' => [

                        ]
                    ]);
                }

                return User::find($jwt->sub);
            }
        });
    }
}
