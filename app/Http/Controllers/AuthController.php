<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminInformationResource;
use App\Http\Resources\CustomerInformationResource;
use App\Http\Resources\UserResource;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api')->only(
            'is_verified',
            'profile',
            'logout'
        );
    }

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
        $user->password = Crypt::encryptString($request->password);
        $user->save();

        $fcm_token = new FcmToken();
        $fcm_token->user_id = $user->id;
        $fcm_token->fcm_token = $request->fcm_token;
        $fcm_token->save();

        $access_token = $user->createToken('authToken')->accessToken;

        $verification_code = Str::random(30); //Generate verification code
        DB::table('verifications')->insert(['user_id' => $user->id, 'verification_code' => $verification_code]);

        $email = $request->email;
        Mail::to($email)->send(new Verify($verification_code));

        return $this->okResponse(
            new UserResource([
                'register',
                $access_token
            ]),
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
            return view('success_verify')->with('msg', 'Thank you! Your account has been successfully verified..');
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
        $user_password = Crypt::decryptString($user->password);
        //Check Password
        if (!$user || !($user_password == $request->password)) {
            return $this->unauthorizedResponse(null, 'Bad Creds');
        }
        $access_token = $user->createToken('API Token')->accessToken;

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

        return $this->okResponse(
            new UserResource([
                'login',
                $access_token
            ]),
            'logged in successfully'
        );
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
            $access_token = $newuser->createToken('API Token')->accessToken;

            $fcm_token = new FcmToken();
            $fcm_token->user_id = $newuser->id;
            $fcm_token->fcm_token = $request->fcm_token;
            $fcm_token->save();

            return $this->okResponse(
                new UserResource([
                    'register',
                    $access_token
                ]),
                ''
            );
        }
        //else
        if ($request->provider_id == $user->provider_id) {

            $access_token = $user->createToken('API Token')->accessToken;

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

            return $this->okResponse(
                new UserResource([
                    'login',
                    $user,
                    $request->fcm_token,
                    $access_token
                ]),
                ''
            );
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

        FcmToken::where('user_id', Auth::id())->where('fcm_token', $request->fcm_token)->delete();

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
            return $this->okResponse(new AdminInformationResource(
                $user_info
            ), 'user Information');
        } else {
            $user_info = CustomerInformation::where('user_id', Auth::id())->first();
            return $this->okResponse(new CustomerInformationResource(
                $user_info
            ), 'user Information');
        }
    }

    public function is_verified(){
        $user = User::findOrFail(Auth::id());
        if($user->is_verified == true){
            return $this->okResponse(["verified"=> 1],"email verified successfully");
        }else{
            return response(["verified"=> 0],"The account has not yet been verified");
        }
    }
}
