<?php

namespace App\Http\Controllers;

use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sign_in(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required_without:username',
            'username' => 'required_without:email',
            'password' =>'required'
        ]);

        $user = User::where('email', $request->input('email'))
            ->orWhere('username', $request->input('username'))
            ->first();

        if (!$user) {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'message' => 'Bad Request'
                ], 'data' => [

                ]
            ]);
        }

        if ($request->password == Crypt::decrypt($user->password)) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'jwt' => $jwt = JWT::encode([
                        'iss' => 'task-02-code',
                        'sub' => $user->id,
                        'iat' => time(),
                        'exp' => time() + 60*60
                    ], env('JWT_SECRET')),
                    'message' => 'OK'
                ], 'data' => [

                ]
            ]);
        } else {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'message' => 'Bad Request'
                ], 'data' => [

                ]
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sign_up(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' =>'confirmed|required'
        ]);

        $user = new User;

        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Crypt::encrypt($request->password);

        $user->save();

        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'OK'
            ], 'data' => [
                'user' => $user
            ]
        ]);
    }
}
