<div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Status Nodes</h2>
            
            <div class="flex items-center space-x-2">
                <button 
                    wire:click="resetFilters"
                    class="px-3 py-1 text-xs text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none"
                >
                    Reset
                </button>
            </div>
        </div>
        
        <!-- Status Summary -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $statusSummary['total'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Nodes</div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $statusSummary['normal'] }}</div>
                <div class="text-sm text-green-500 dark:text-green-300">Normal</div>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $statusSummary['warning'] }}</div>
                <div class="text-sm text-yellow-500 dark:text-yellow-300">Warning</div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $statusSummary['critical'] }}</div>
                <div class="text-sm text-red-500 dark:text-red-300">Critical</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="w-full sm:w-64">
                <input 
                    wire:model.live.debounce.300ms="search"
                    type="text" 
                    placeholder="Search nodes..." 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div class="w-full sm:w-auto">
                <select 
                    wire:model.live="environment"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Environments</option>
                    @foreach($environments as $env)
                        <option value="{{ $env }}">{{ $env }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-auto">
                <select 
                    wire:model.live="region"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Regions</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg }}">{{ $reg }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <!-- Nodes Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                            Name
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('environment')">
                            Environment
                            @if($sortField === 'environment')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('region')">
                            Region
                            @if($sortField === 'region')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('last_seen_at')">
                            Last Seen
                            @if($sortField === 'last_seen_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($nodes as $node)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $node->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $node->environment ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $node->region ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($node->status === 'normal')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        Normal
                                    </span>
                                @elseif($node->status === 'warning')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                        Warning
                                    </span>
                                @elseif($node->status === 'critical')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                        Critical
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        {{ $node->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $node->last_seen_at ? $node->last_seen_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('nodes.details', $node->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                No status nodes found. Try adjusting your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>