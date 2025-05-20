<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Node Comparison</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Node Selection -->
            <div class="lg:col-span-2">
                <label for="nodes-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Nodes to Compare (minimum 2)</label>
                <select 
                    id="nodes-select" 
                    wire:model.live="selectedNodeIds" 
                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    multiple
                >
                    @foreach($nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->environment }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple nodes</p>
            </div>
            
            <!-- Metric Selection -->
            <div>
                <label for="metric-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Metric</label>
                <select 
                    id="metric-select" 
                    wire:model.live="selectedMetric" 
                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">Select a metric</option>
                    @foreach($availableMetrics as $metric)
                        <option value="{{ $metric }}">{{ $metric }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Period Type Selection -->
            <div>
                <label for="period-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period</label>
                <select 
                    id="period-select" 
                    wire:model.live="periodType" 
                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="hourly">Hourly</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Date Range Selection -->
            <div>
                <label for="start-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                <input 
                    type="date" 
                    id="start-date" 
                    wire:model.live="startDate" 
                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>
            <div>
                <label for="end-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                <input 
                    type="date" 
                    id="end-date" 
                    wire:model.live="endDate" 
                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
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
                    <p class="text-gray-500 dark:text-gray-400">Loading comparison data...</p>
                </div>
            </div>
        @elseif(empty($comparisonData) || count($selectedNodeIds) < 2 || empty($comparisonData['labels'] ?? []))
            <div class="flex justify-center items-center h-80 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-center p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No comparison data available</p>
                    <p class="text-gray-400 dark:text-gray-500 mt-2">Select at least 2 nodes and a metric to compare</p>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 h-80 mb-6">
                <canvas id="comparison-chart" wire:ignore></canvas>
            </div>
            
            <!-- Summary Table -->
            @if(!empty($comparisonData['summary']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Node</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Min Value</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Average Value</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Max Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($comparisonData['summary'] as $summary)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="h-4 w-4 rounded-full mr-2" style="background-color: {{ $summary['color'] }}"></span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $summary['nodeName'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-800 dark:text-gray-200">{{ $summary['min'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-800 dark:text-gray-200">{{ $summary['avg'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-800 dark:text-gray-200">{{ $summary['max'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            
            <!-- Chart.js Initialization -->
            <script>
                document.addEventListener('livewire:initialized', function () {
                    let comparisonChart = null;
                    
                    function initializeComparisonChart(data) {
                        if (comparisonChart) {
                            comparisonChart.destroy();
                        }
                        
                        const ctx = document.getElementById('comparison-chart').getContext('2d');
                        comparisonChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: data.datasets
                            },
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
                    if (@json($comparisonData) && @json($comparisonData['labels'] ?? []).length > 0) {
                        initializeComparisonChart(@json($comparisonData));
                    }
                    
                    // Listen for updates to the chart data
                    @this.on('comparisonDataUpdated', function(data) {
                        initializeComparisonChart(data);
                    });
                });
            </script>
        @endif
    </div>
</div>
