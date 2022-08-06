<?php

namespace App\Listeners;

use App\Events\OrderStored;
use App\Http\Controllers\NotificationController;
use App\Models\FcmToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminAnOrderStored
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
     * @param  \App\Events\OrderStored  $event
     * @return void
     */
    public function handle(OrderStored $event)
    {
        //ارسال اشعار لصاحب المكتبة بانه تم تسجيل طلب في مكتبته
        $library_fcm_token = FcmToken::where('user_id', $event->order->library_id)->get();
        foreach ($library_fcm_token as $fcm) {
            (new NotificationController)->send_Notification(
                $fcm->fcm_token,
                'Order',
                'A request has been registered from your library,
                 please review the requests section'
            );
        }
    }
}
