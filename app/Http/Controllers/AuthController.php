<?php

namespace App\Http\Controllers;

use App\Mail\Verify;
use App\Models\Address;
use App\Models\User;
use App\Models\AdminInformation;
use App\Models\CustomerInformation;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\ApiResponder;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    use ApiResponder;
    //

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|numeric',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }


        $user = new User();
        $user->role_id = $request->role_id;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        $fcm_token = new FcmToken();
        $fcm_token->user_id = $user->id;
        $fcm_token->fcm_token = $request->fcm_token;
        $fcm_token->save();

        $accessToken = $user->createToken('authToken')->accessToken;

        $verification_code = Str::random(30); //Generate verification code
        DB::table('verifications')->insert(['user_id' => $user->id, 'verification_code' => $verification_code]);

        $subject = "Please verify your email address.";
        $email = $request->email;
        // Mail::send(
        //     'verifyAccount',
        //     ['verification_code' => $verification_code],
        //     function ($mail) use ($email, $subject) {
        //         $mail->to('ramamhram093@gmail.com')->subject($subject);
        //     }
        // );
        Mail::to('ramamhrama093@gmail.com')->send(new Verify($verification_code));
        return $this->okResponse(
            [
                'user' => $user,
                'fcm_token' => $request->fcm_token,
                'access token' => $accessToken
            ],
            'Thanks for signing up! Please check your email to complete your registration.'
        );
    }

    public function verify($verification_code)
    {
        $check = DB::table('verifications')->where('verification_code', $verification_code)->first();

        if (!is_null($check)) {
            $user = User::find($check->user_id);

            $user->update(['is_verified' => 1]);
            DB::table('verifications')->where('verification_code', $verification_code)->delete();
            return view('success_verify')->with('msg','Thank you! Your account has been successfully verified..');
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        //Check Email
        $user = User::where('email', $request->email)->first();
        //Check Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->unauthorizedResponse(null, 'Bad Creds');
        }
        $token = $user->createToken('API Token')->accessToken;

        $fcm_token = new FcmToken();
        $fcm_token->user_id = $user->id;
        $fcm_token->fcm_token = $request->fcm_token;
        $fcm_token->save();

        if ($user->role_id == 1) {
            $user_info = AdminInformation::where('user_id', $user->id)->first();
            $user_info->update([
                $user_info->status = 'online'
            ]);
        }

        $response = [
            'user' => $user,
            'token' => $token,
            'fcm_token' => $request->fcm_token
        ];

        return $this->okResponse($response, 'logged in successfully');
    }

    public function loginOrRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'provider_id' => 'required|numeric',
            'role_id' => 'required|numeric',
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $newuser = new User();
            $newuser->email = $request->email;
            $newuser->is_verified = 1;
            $newuser->provider_id = $request->provider_id;
            $newuser->role_id = $request->role_id;
            $newuser->save();
            $token = $newuser->createToken('API Token')->accessToken;

            $fcm_token = new FcmToken();
            $fcm_token->user_id = $newuser->id;
            $fcm_token->fcm_token = $request->fcm_token;
            $fcm_token->save();

            return $this->okResponse([
                'register' => 'true',
                'user' => $newuser,
                'toke' => $token,
                'fcm_token' => $request->fcm_token
            ], '');
        }
        //else
        if ($request->provider_id == $user->provider_id) {

            $token = $user->createToken('API Token')->accessToken;

            $fcm_token = new FcmToken();
            $fcm_token->user_id = $user->id;
            $fcm_token->fcm_token = $request->fcm_token;
            $fcm_token->save();

            if ($user->role_id == 1) {
                $user_info = AdminInformation::where('user_id', $user->id)->first();
                $user_info->update([
                    $user_info->status = 'online'
                ]);
            }

            return $this->okResponse([
                'login' => 'true',
                'user' => $user,
                'token' => $token,
                'fcm_token' => $request->fcm_token
            ], '');
        } else {
            return $this->unauthorizedResponse(null, 'Bad Creds');
        }
    }

    public function logout(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        FcmToken::where('user_id',Auth::id())->where('fcm_token', $request->fcm_token)->delete();

        $user = $request->user();

        if ($user->role_id == 1) {
            $user_info = AdminInformation::where('user_id', $user->id)->first();
            $user_info->update([$user_info->status = 'offline']);
        }

        $user->tokens()->delete();
        return $this->okResponse(null, 'Logged out');
    }

    public function profile()
    {
        $user = User::find(Auth::id());
        if ($user->role_id == 1) {
            $user_info = AdminInformation::where('user_id', Auth::id())->first();
        } else {
            $user_info = CustomerInformation::where('user_id', Auth::id())->first();
        }

        $response = [
            'user' => $user,
            'user_info' => $user_info
        ];
        return $this->okResponse($response, 'user Information');
    }

}
