<?php

namespace App\Http\Controllers\ApiAuthorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) 
        {

            return response()->json(['error' => 'UnAuthorised'], 401);
            
        }

        $accessToken = Auth::user()->createToken('accessToken')->accessToken;

        return response()->json(['success'=>'Successfully Login','access_token' => $accessToken], 200);
    }



    public function register(RegisterRequest $request)
    {
        
        $user = User::create([

            'name' => $request->name,

            'email' => $request->email,

            'password' => bcrypt($request->password)

        ]);

        $accessToken = $user->createToken('apiauth')->accessToken;    

        return response()->json(['token' => $accessToken], 200);
    }

    public function logout(LoginRequest $request)
    {
        $request->user()->tokens()->delete();
    }
}
