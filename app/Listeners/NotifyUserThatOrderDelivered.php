<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Http\Controllers\NotificationController;
use App\Models\AdminInformation;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserThatOrderDelivered
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderDelivered  $event
     * @return void
     */
    public function handle(OrderDelivered $event)
    {
        //ارسال اشعار للمستخدم انه تم توصيل طلبه
        $user_fcm_token = FcmToken::where('user_id', $event->order->user_id)->get();
        $library = AdminInformation::where('user_id', $event->order->library_id)->first();
        foreach ($user_fcm_token as $fcm) {
            (new NotificationController)->send_Notification(
                $fcm->fcm_token,
                $library->library_name . ' Library',
                'Your order has been delivered'
            );
        }
    }
}
