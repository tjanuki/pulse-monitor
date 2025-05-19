<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $node->name }} - Pulse Monitor</title>

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
                            <a href="{{ route('dashboard') }}" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
                    <!-- Back Button -->
                    <div class="mb-6">
                        <a href="{{ route('dashboard') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                    
                    <!-- Node Header -->
                    <div class="mb-8 flex items-center">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $node->name }}</h1>
                        <span class="ml-4 px-3 py-1 text-sm rounded-full 
                            {{ $node->status === 'normal' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 
                               ($node->status === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                                'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200') }}">
                            {{ ucfirst($node->status) }}
                        </span>
                    </div>
                    
                    <!-- Node Metrics Component -->
                    <div>
                        @livewire('node-metrics-card', ['nodeId' => $node->id])
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    @livewireScripts
</body>
</html>