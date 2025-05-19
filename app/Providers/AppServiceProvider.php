<?php

namespace App\Providers;

use Livewire\Livewire;
use App\Livewire\StatusNodesCard;
use App\Livewire\NodeMetricsCard;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Livewire components
        Livewire::component('status-nodes-card', StatusNodesCard::class);
        Livewire::component('node-metrics-card', NodeMetricsCard::class);
    }
}
