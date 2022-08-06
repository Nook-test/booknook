<?php

namespace App\Console\Commands;

use App\Http\Controllers\NotificationController;
use App\Models\BorrowProcess;
use Illuminate\Console\Command;

class ReminderWhenToReturnBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReminderWhenToReturnBook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder when to return the book';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users_devices = BorrowProcess::join('orders', 'orders.id', 'borrow_proccesses.order_id')
            ->join('fcm_tokens', 'fcm_tokens.user_id', 'order.user_id')
            ->join('library_books', 'library_books.id', 'borrow_proccesses.book_id')
            ->join('books', 'books.id', 'library_books.book_id')
            ->join('admin_information', 'admin_information.user_id', 'books.library_id')
            ->where('borrow_proccesses.returned', 0)
            ->get([
                'borrow_proccesses.created_at',
                'fcm_tokens.fcm_token',
                'books.name',
                'admin_information.libraryName'
            ]);


        foreach ($users_devices as $user_device) {

            $remaining_days = 30 - (now()->diffInDays($user_device[0]));

            if ($remaining_days < 28) {
                (new NotificationController)
                    ->send_Notification(
                        $user_device[1],
                        'Reminder',
                        'You have to return the ' . $user_device[2] . ' book to ' . $user_device[3] . ' library after ' . $remaining_days . ' days'
                    );
            } else if ($remaining_days >= 28 && $remaining_days <= 30) {
                (new NotificationController)
                    ->send_Notification(
                        $user_device[1],
                        'Reminder',
                        'You have to return the ' . $user_device[2] . ' book to ' . $user_device[3] . ' library after two days max'
                    );
            }
        }
    }
}
