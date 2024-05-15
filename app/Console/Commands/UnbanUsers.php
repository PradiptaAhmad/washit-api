<?php

namespace App\Console\Commands;

use App\Models\BannedUser;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnbanUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unban-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        BannedUser::where('unbanned_at', '>', Carbon::now())->delete();
    }
}
