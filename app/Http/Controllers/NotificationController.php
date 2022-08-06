<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use ApiResponder;

    public function send_Notification($token, $title, $body)
    {
        $SERVER_API_KEY = 'AAAAHetChBg:APA91bF7JvHDgVt7cV9bkDtYhM-UkO4cdqY2QI99ai9soegH5wBjCGpHPp4ou1S-k_L6OG8RZ8hhsT9LwxSA4h1mGM5khdxT-JYo505kTtsgVvyyYMgmECmPizdFJbK1SOgWG7NFzPaI';

        $token = $token;

        $data = [

            "registration_ids" => [
                $token
            ],

            "notification" => [

                "title" => $title,

                "body" => $body,

            ],

        ];

        $dataString = json_encode($data);

        $headers = [

            'Authorization: key=' . $SERVER_API_KEY,

            'Content-Type: application/json',

        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        curl_exec($ch);
    }

    public function change_fcm_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'old_fcm_token' => 'required|string',
            'new_fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $user_fcm_token = FcmToken::where('user_id', $request->user_id)->where('fcm_token', $request->old_fcm_token)->first();
        $user_fcm_token->update([
            'fcm_token' => $request->new_fcm_token
        ]);

        return $this->okResponse(null, 'fcm token changed successfully');
    }
}
