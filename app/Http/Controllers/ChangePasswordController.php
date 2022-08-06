<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    use ApiResponder;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ]);
        }

        $user = User::findOrFail(Auth::id());
        $user_password =  Crypt::decryptString($user->password);
        if (!($user_password == $request->old_password)) {
            return $this->unauthorizedResponse(null, 'Bad Creds');
        } else {
            $user->update([
                $user->password = bcrypt($request->new_password)
            ]);
            return $this->okResponse(null, 'password changed successfully');
        }
    }
}
