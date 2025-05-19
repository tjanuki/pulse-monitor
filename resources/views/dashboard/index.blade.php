<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pulse Monitor - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pulse Monitor</h1>
                        </div>
                        <div class="ml-6 flex space-x-8">
                            <a href="{{ route('dashboard') }}" class="border-indigo-500 text-gray-900 dark:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="/pulse" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Laravel Pulse
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <div class="py-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Stats Overview -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">System Overview</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Nodes Stats -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-500 dark:bg-indigo-600 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Total Nodes
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                                                        {{ $stats['totalNodes'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                                    <div class="text-sm">
                                        <span class="font-medium text-green-600 dark:text-green-400">{{ $stats['normalNodes'] }} Normal</span>
                                        <span class="mx-2 text-gray-500 dark:text-gray-400">|</span>
                                        <span class="font-medium text-yellow-600 dark:text-yellow-400">{{ $stats['warningNodes'] }} Warning</span>
                                        <span class="mx-2 text-gray-500 dark:text-gray-400">|</span>
                                        <span class="font-medium text-red-600 dark:text-red-400">{{ $stats['criticalNodes'] }} Critical</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Metrics Stats -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 dark:bg-green-600 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Total Metrics
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                                                        {{ $stats['totalMetrics'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                                    <div class="text-sm">
                                        <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $stats['uniqueMetricNames'] }} Unique Metrics</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thresholds Stats -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 dark:bg-yellow-600 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Configured Thresholds
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                                                        {{ $stats['configuredThresholds'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                                    <div class="text-sm">
                                        <a href="#" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300">
                                            View all thresholds
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Health Status -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 rounded-md p-3 {{ $stats['criticalNodes'] > 0 ? 'bg-red-500 dark:bg-red-600' : ($stats['warningNodes'] > 0 ? 'bg-yellow-500 dark:bg-yellow-600' : 'bg-green-500 dark:bg-green-600') }}">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    System Health
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                                                        @if($stats['criticalNodes'] > 0)
                                                            Critical
                                                        @elseif($stats['warningNodes'] > 0)
                                                            Warning
                                                        @else
                                                            Healthy
                                                        @endif
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                                    <div class="text-sm">
                                        <span class="font-medium {{ $stats['criticalNodes'] > 0 ? 'text-red-600 dark:text-red-400' : ($stats['warningNodes'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                            {{ $stats['criticalNodes'] > 0 ? $stats['criticalNodes'] . ' nodes in critical state' : ($stats['warningNodes'] > 0 ? $stats['warningNodes'] . ' nodes in warning state' : 'All systems operational') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Nodes Component -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Status Nodes</h2>
                        @livewire('status-nodes-card')
                    </div>
                    
                    @if($criticalNodes->count() > 0)
                    <!-- Critical Nodes -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Critical Nodes</h2>
                        
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($criticalNodes as $node)
                                <li>
                                    <a href="{{ route('nodes.details', $node->id) }}" class="block hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <div class="px-6 py-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-lg font-medium text-gray-900 dark:text-white">
                                                        {{ $node->name }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $node->environment ?? 'No Environment' }} / {{ $node->region ?? 'No Region' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                        Critical
                                                    </span>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        Last seen: {{ $node->last_seen_at ? $node->last_seen_at->diffForHumans() : 'Never' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Recent Metrics -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Recent Metrics</h2>
                        
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Node
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Metric
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Group
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Value
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Recorded At
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($recentMetrics as $metric)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                <a href="{{ route('nodes.details', $metric->node->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">
                                                    {{ $metric->node->name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $metric->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $metric->group ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $metric->value }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($metric->status === 'normal')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                        Normal
                                                    </span>
                                                @elseif($metric->status === 'warning')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                                        Warning
                                                    </span>
                                                @elseif($metric->status === 'critical')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                        Critical
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                        {{ $metric->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $metric->recorded_at->format('Y-m-d H:i:s') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    @livewireScripts
</body>
</html>