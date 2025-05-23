<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">
                Metrics for {{ $node->name }}
                <span class="text-sm font-normal text-gray-500 ml-2">
                    @if($node->status === 'normal')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Normal
                        </span>
                    @elseif($node->status === 'warning')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Warning
                        </span>
                    @elseif($node->status === 'critical')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Critical
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $node->status }}
                        </span>
                    @endif
                </span>
            </h2>
            
            <div class="flex items-center space-x-2">
                <button 
                    wire:click="resetFilters"
                    class="px-3 py-1 text-xs text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none"
                >
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Node Info -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Environment</div>
                <div class="text-lg font-medium text-gray-700">{{ $node->environment ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Region</div>
                <div class="text-lg font-medium text-gray-700">{{ $node->region ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Last Seen</div>
                <div class="text-lg font-medium text-gray-700">
                    {{ $node->last_seen_at ? $node->last_seen_at->diffForHumans() : 'Never' }}
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="w-full sm:w-auto">
                <select 
                    wire:model.live="selectedGroup"
                    class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Metric Groups</option>
                    @foreach($metricGroups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-auto">
                <select 
                    wire:model.live="selectedMetric"
                    class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Metrics</option>
                    @foreach($metricNames as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-auto">
                <select 
                    wire:model.live="timeRange"
                    class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="1">Last Hour</option>
                    <option value="6">Last 6 Hours</option>
                    <option value="12">Last 12 Hours</option>
                    <option value="24">Last 24 Hours</option>
                    <option value="72">Last 3 Days</option>
                    <option value="168">Last Week</option>
                </select>
            </div>
        </div>
        
        <!-- Chart for selected metric -->
        @if($selectedMetric && count($timeSeriesData['labels']) > 0)
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-700 mb-4">{{ $selectedMetric }} Trend</h3>
                <div class="h-64">
                    <!-- We would use a JS chart library here (Chart.js, ApexCharts, etc) -->
                    <div class="text-center text-gray-500 py-10">
                        [Chart visualization would be implemented here with {{ count($timeSeriesData['labels']) }} data points]
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Latest Metrics Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Group
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Value
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Recorded At
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($latestMetrics as $metric)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $metric->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $metric->group ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $metric->value }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($metric->status === 'normal')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Normal
                                    </span>
                                @elseif($metric->status === 'warning')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Warning
                                    </span>
                                @elseif($metric->status === 'critical')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Critical
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $metric->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $metric->recorded_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No metrics found. Try adjusting your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>