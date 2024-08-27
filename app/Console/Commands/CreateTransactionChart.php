<?php

namespace App\Console\Commands;

use App\Models\TransactionChart;
use Illuminate\Console\Command;

class CreateTransactionChart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-transaction-chart';

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
        TransactionChart::create([
            'total_transactions' => 0,
            'total_income' => 0,
        ]);
    }
}
