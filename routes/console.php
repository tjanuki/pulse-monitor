<?php

use App\Models\StatusNode;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled commands for the pulse monitor
| application. These commands collect metrics from registered status nodes
| and process them at regular intervals.
|
*/

// Schedule metrics collection for all registered status nodes
Schedule::command('metrics:collect-all')
    ->description('Collect all metrics for all status nodes')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/metrics-collection.log'));

// Helper command to collect metrics for all registered nodes
Artisan::command('metrics:collect-all', function () {
    $statusNodes = StatusNode::all();
    
    if ($statusNodes->isEmpty()) {
        $this->warn('No status nodes found to collect metrics for.');
        return;
    }
    
    $this->info('Collecting metrics for ' . $statusNodes->count() . ' status nodes...');
    
    foreach ($statusNodes as $node) {
        $this->info("Collecting metrics for node: {$node->name}");
        
        // Collect CPU metrics
        $this->call('metrics:collect-cpu', ['node_id' => $node->id]);
        
        // Collect memory metrics
        $this->call('metrics:collect-memory', ['node_id' => $node->id]);
        
        // Collect disk metrics
        $this->call('metrics:collect-disk', ['node_id' => $node->id]);
    }
    
    $this->info('All metrics collected successfully.');
})->purpose('Collect all metrics for all registered status nodes');
