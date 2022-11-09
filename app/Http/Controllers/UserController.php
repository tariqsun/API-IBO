<?php

namespace App\Http\Controllers;

use App\Jobs\RecoveryElertJob;
use App\Mail\RecoveryAlert;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function user(Request $request)
    {
        return $request->user()->only('id', 'name', 'email');
    }

    public function login(Request $request)
    {

        $request->validate([
            'email'=>['required', 'email'],
            'password'=>['required']
        ]);

        if(Auth::attempt(['email'=>$request->email, 'password'=>$request->password])){
            $token = Auth::user()->createToken('token');
            $user = auth()->user()->only('id', 'name', 'email');
            return ['errors'=>false, 'token'=>$token->plainTextToken, 'user'=>$user];
        }else{
            return ['errors'=>true, 'message'=>'invalid credentials'];
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'=>['required'],
            'email'=>['required', 'email', 'unique:users'],
            'password'=>['required', 'confirmed']
        ]);

        $user_create = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);

        if($user_create){
            Auth::attempt($request->only('email', 'password'));
            $token = Auth::user()->createToken('token');
            $user = auth()->user()->only('id', 'name', 'email');
            return response()->json(['errors'=>false, 'token'=>$token->plainTextToken, 'user'=>$user]);
        }else{
            return response()->json(['errors'=>true, 'message'=>'Some Thing went wrong']);

        }
    }

    public function logout(Request $request)
    {
       return $request->user()->currentAccessToken()->delete();
    }

    public function recoveryAlert()
    {
        dispatch(new RecoveryElertJob(User::first()))->delay(now()->addSecond(30));
    }
}
