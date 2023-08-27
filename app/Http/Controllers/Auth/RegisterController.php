<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string',
            'mobile'        => 'required|string',
            'password'      => 'required|string',
            'email'         => 'required|email',
        ]);
// return $request;
        $user = User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user = $user->fresh();
        $token = $user->createToken('MyApp');
        $user['token'] =  $token->accessToken;
        $user['token_expire_in'] = $token->token->expires_at->timestamp;

        return response()->json(['user' => $user->toArray()], 200);
    }

}
