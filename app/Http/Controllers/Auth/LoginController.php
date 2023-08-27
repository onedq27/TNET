<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// use App\Providers\RouteServiceProvider;
// use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|exists:users',
            'password' => 'required',
        ]);

        if(Auth::attempt(['mobile' => $request->mobile, 'password' => $request->password])){
            $user = Auth::user();
            $token = $user->createToken('MyApp');
            $user['token'] =  $token->accessToken;
            $user['token_expire_in'] = $token->token->expires_at->timestamp;
            return response()->json(['user' => $user], 200);
        }
        
        return response()->json(['error'=>'Unauthorised'], 401);
    }
}
