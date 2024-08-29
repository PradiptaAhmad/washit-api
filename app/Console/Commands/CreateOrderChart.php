<?php

namespace App\Console\Commands;

use App\Models\OrderChart;
use Illuminate\Console\Command;

class CreateOrderChart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-order-chart';

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
        $orderChart = OrderChart::whereDate('created_at', now()->format('Y-m-d'))->first();
        if ($orderChart == null) {
            OrderChart::create([
                'total_orders' => 0,
            ]);
            return;
        }
        return;
    }
}
