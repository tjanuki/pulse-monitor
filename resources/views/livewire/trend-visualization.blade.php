<div class="p-6 bg-white rounded-lg shadow-md">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Metrics Trend Visualization</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Node Selection -->
            <div>
                <label for="node-select" class="block text-sm font-medium text-gray-700 mb-1">Node</label>
                <select 
                    id="node-select" 
                    wire:model.live="nodeId" 
                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">Select a node</option>
                    @foreach($nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->environment }})</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Metric Selection -->
            <div>
                <label for="metric-select" class="block text-sm font-medium text-gray-700 mb-1">Metric</label>
                <select 
                    id="metric-select" 
                    wire:model.live="metricName" 
                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    @if(empty($availableMetrics)) disabled @endif
                >
                    <option value="">Select a metric</option>
                    @foreach($availableMetrics as $metric)
                        <option value="{{ $metric }}">{{ $metric }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Period Type Selection -->
            <div>
                <label for="period-select" class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                <select 
                    id="period-select" 
                    wire:model.live="periodType" 
                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="hourly">Hourly</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            
            <!-- Date Range Selection -->
            <div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="start-date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input 
                            type="datetime-local" 
                            id="start-date" 
                            wire:model.live="startDate" 
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                    <div>
                        <label for="end-date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input 
                            type="datetime-local" 
                            id="end-date" 
                            wire:model.live="endDate" 
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart Container -->
    <div>
        @if($isLoading)
            <div class="flex justify-center items-center h-80">
                <div class="text-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-500">Loading chart data...</p>
                </div>
            </div>
        @elseif(empty($chartData) || empty($chartData['labels'] ?? []))
            <div class="flex justify-center items-center h-80 bg-gray-50 rounded-lg">
                <div class="text-center p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-gray-500 text-lg">No data available</p>
                    <p class="text-gray-400 mt-2">Select a node and metric to view trend data</p>
                </div>
            </div>
        @else
            <div class="bg-white p-4 rounded-lg border border-gray-200 h-80">
                <canvas id="metrics-chart" wire:ignore></canvas>
            </div>
            
            <!-- Chart.js Initialization -->
            <script>
                document.addEventListener('livewire:initialized', function () {
                    let chart = null;
                    
                    function initializeChart(data) {
                        if (chart) {
                            chart.destroy();
                        }
                        
                        const ctx = document.getElementById('metrics-chart').getContext('2d');
                        chart = new Chart(ctx, {
                            type: 'line',
                            data: data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        enabled: true,
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            color: 'rgba(160, 174, 192, 0.1)',
                                        }
                                    },
                                    y: {
                                        grid: {
                                            color: 'rgba(160, 174, 192, 0.1)',
                                        },
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                    
                    // Initialize with current data
                    if (@json($chartData) && @json($chartData['labels'] ?? []).length > 0) {
                        initializeChart(@json($chartData));
                    }
                    
                    // Listen for updates to the chart data
                    Livewire.on('chartDataUpdated', function(chartData) {
                        initializeChart(chartData);
                    });
                    
                    @this.on('chartDataUpdated', function(chartData) {
                        initializeChart(chartData);
                    });
                });
            </script>
        @endif
    </div>
</div>
