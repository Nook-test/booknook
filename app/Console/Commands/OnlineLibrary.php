<?php

namespace App\Console\Commands;

use App\Models\AdminInformation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OnlineLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onlineLibrary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert the status of the library to opened at its opening time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $libraries = AdminInformation::all();
        foreach ($libraries as $library) {
            $date = Carbon::parse(now('GMT+03:00'))->format('H:i');
            $a = Carbon::parse($library->open_time)->format('H:i');
            if($date == $a){
                $library->update([
                    $library->status = 'online'
                ]);
            }
        }
    }
}
