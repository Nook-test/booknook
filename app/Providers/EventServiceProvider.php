<?php

namespace App\Providers;

use App\Events\OrderCanceled;
use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Events\OrderStored;
use App\Listeners\NotifyAdminAnOrderStored;
use App\Listeners\NotifyUserThatOrderCanceled;
use App\Listeners\NotifyUserThatOrderConfirmed;
use App\Listeners\NotifyUserThatOrderDelivered;
use App\Listeners\NotifyUserThatOrderStored;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderStored::class => [
            NotifyAdminAnOrderStored::class,
            NotifyUserThatOrderStored::class
        ],
        OrderConfirmed::class => [
            NotifyUserThatOrderConfirmed::class
        ],
        OrderDelivered::class => [
            NotifyUserThatOrderDelivered::class
        ],
        OrderCanceled::class => [
            NotifyUserThatOrderCanceled::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
