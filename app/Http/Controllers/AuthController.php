<?php

namespace App\Http\Controllers;

use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

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
        $rules = [
            'email' => 'email|required_without:username',
            'password' =>'required',
            'username' => 'required_without:email'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->failure(422, [], $validator->errors()->toArray(), $request);
        }

        $user = User::where('email', $request->input('email'))
            ->orWhere('username', $request->input('username'))
            ->first();

        if (!$user) {
            return response()->failure(404, [], ['Not Found.'], $request);
        }

        if ($request->password == Crypt::decrypt($user->password)) {
            return response()->success(200, ['api_token' => JWT::encode([
                'iss' => 'task-02-code',
                'sub' => $user->id,
                'iat' => time(),
                'exp' => time() + 600*600
            ], env('JWT_SECRET'))], ['OK.'], $request);
        } else {
            return response()->failure(404, [], ['Not Found.'], $request);
        }
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function sign_up(Request $request)
    {
        $rules = [
            'email' => 'email|required|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' =>'confirmed|required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->failure(422, [], $validator->errors()->toArray(), $request);
        }

        $user = new User;

        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Crypt::encrypt($request->password);

        $user->save();

        return response()->success(201, ['user' => $user], ['Created.'], $request);
    }
}
