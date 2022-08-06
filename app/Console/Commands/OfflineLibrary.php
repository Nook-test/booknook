<?php

namespace App\Console\Commands;

use App\Models\AdminInformation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OfflineLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offlineLibrary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert the status of the library to closed at its closing time';

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
            $a = Carbon::parse($library->close_time)->format('H:i');
            if($date == $a){
                $library->update([
                    $library->status = 'offline'
                ]);
            }
        }
    }
}
