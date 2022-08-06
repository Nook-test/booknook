<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    use ApiResponder;

    public function forgot(){
        $cred = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($cred);

        return $this->okResponse(null,'Reset Password Link Sent On Your Email' );
    }

    public function reset(Request $request){
        $validator = Validator::make($request->all(), [
            'email'=> 'required|string',
            'password'=> 'required|string|confirmed|min:6',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return view('fail_reset_password')->with('Error','Invalid Request');
        }

        $cred = [
            'email' => $request->email,
            'password' => $request->password,
            'token' => $request->token,
        ];


        $email_password_status = Password::reset($cred,function($user , $password){
            $user->password = Crypt::encryptString($password);
            $user->save();
        });

        if($email_password_status == Password::INVALID_TOKEN){
            return view('fail_reset_password')->with('Error','Invalid Token');
        }

        return view('success_reset_password');

    }
}
