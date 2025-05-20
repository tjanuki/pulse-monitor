@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Metrics Trends & Analysis</h1>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            Back to Dashboard
        </a>
    </div>

    <div class="space-y-8">
        <!-- Trend Visualization Component -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Metrics Trend Analysis</h2>
                <p class="mb-6 text-gray-700 dark:text-gray-300">
                    View historical trends for node metrics. Select a node, metric, and time period to visualize the data.
                </p>
                
                <livewire:trend-visualization />
            </div>
        </div>
        
        <!-- Node Comparison Component -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Node Comparison</h2>
                <p class="mb-6 text-gray-700 dark:text-gray-300">
                    Compare the same metric across multiple nodes to identify performance variations and outliers.
                </p>
                
                <livewire:nodes-comparison />
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js for visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection