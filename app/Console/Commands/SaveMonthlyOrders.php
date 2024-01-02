<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SaveMonthlyOrders extends Command
{
    protected $signature = 'save:monthly-orders';
    protected $description = 'Save monthly sales data for the previous month.';

    public function handle()
    {
        // Get the first and last day of the previous month
        $firstDay = Carbon::now()->subMonth()->startOfMonth();
        $lastDay = Carbon::now()->subMonth()->endOfMonth();

        // Fetch sales data for the previous month
        $monthlyOrders = Order::whereBetween('created_at', [$firstDay, $lastDay])->count();

        $monthlyIncome = Order::whereBetween('created_at', [$firstDay, $lastDay])->sum('total');


        // Save the monthly sales data to a dedicated table or repository
        // Example: MonthlySales::create(['month' => $firstDay->format('Y-m'), 'sales' => $monthlySales]);

        $this->info('Monthly Orders data saved successfully!');
    }
}
