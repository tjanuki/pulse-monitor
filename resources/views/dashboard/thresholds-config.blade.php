@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alert Thresholds Configuration</h1>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            Back to Dashboard
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                Configure alert thresholds for different metrics. When a metric value exceeds the warning or critical threshold, 
                the system will generate alerts and notify administrators.
            </p>
            
            <livewire:alert-thresholds-config />
        </div>
    </div>
</div>
@endsection