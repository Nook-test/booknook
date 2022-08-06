<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifiedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verifyEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting UnVerified Emails';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('users')->where('is_verified',0)->delete();
    }
}
