<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Status Nodes</h2>
            
            <div class="flex items-center space-x-2">
                <button 
                    wire:click="resetFilters"
                    class="px-3 py-1 text-xs text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none"
                >
                    Reset
                </button>
            </div>
        </div>
        
        <!-- Status Summary -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-gray-700">{{ $statusSummary['total'] }}</div>
                <div class="text-sm text-gray-500">Total Nodes</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">{{ $statusSummary['normal'] }}</div>
                <div class="text-sm text-green-500">Normal</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $statusSummary['warning'] }}</div>
                <div class="text-sm text-yellow-500">Warning</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-red-600">{{ $statusSummary['critical'] }}</div>
                <div class="text-sm text-red-500">Critical</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="w-full sm:w-64">
                <input 
                    wire:model.live.debounce.300ms="search"
                    type="text" 
                    placeholder="Search nodes..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div class="w-full sm:w-auto">
                <select 
                    wire:model.live="environment"
                    class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                    class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                            Name
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('environment')">
                            Environment
                            @if($sortField === 'environment')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('region')">
                            Region
                            @if($sortField === 'region')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('last_seen_at')">
                            Last Seen
                            @if($sortField === 'last_seen_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($nodes as $node)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $node->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $node->environment ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $node->region ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($node->status === 'normal')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Normal
                                    </span>
                                @elseif($node->status === 'warning')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Warning
                                    </span>
                                @elseif($node->status === 'critical')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Critical
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $node->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $node->last_seen_at ? $node->last_seen_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('nodes.details', $node->id) }}" class="text-indigo-600 hover:text-indigo-900">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No status nodes found. Try adjusting your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>