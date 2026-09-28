<?php

use App\Models\Order;
use App\Services\OrderWorkflow;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('orders:deadlines', function (OrderWorkflow $workflow) {
    $result = $workflow->processDeadlines();
    $this->info("Cancelled {$result['cancelled']}, auto-completed {$result['completed']}.");
})->purpose('Cancel orders past their deadline and auto-complete unanswered publications');

Artisan::command('links:check', function (OrderWorkflow $workflow) {
    $count = 0;
    Order::where('status', Order::COMPLETED)
        ->where('monitor_until', '>', now())
        ->each(function (Order $order) use ($workflow, &$count) {
            $workflow->recheck($order);
            $count++;
        });
    $this->info("Checked $count links.");
})->purpose('Re-check every monitored link');

Schedule::command('orders:deadlines')->hourly();
Schedule::command('links:check')->dailyAt('03:00');
