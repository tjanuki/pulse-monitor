<div class="p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Alert Thresholds Configuration</h2>
        <button 
            wire:click="showCreateThresholdForm"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
            Add New Threshold
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    <!-- Create/Edit Form -->
    @if ($showCreateForm || $editingThresholdId)
        <div class="mb-6 p-4 bg-gray-50 rounded-md">
            <h3 class="mb-4 text-lg font-medium text-gray-900">
                {{ $editingThresholdId ? 'Edit Threshold' : 'Create New Threshold' }}
            </h3>
            <div class="space-y-4">
                <div>
                    <label for="metric_name" class="block text-sm font-medium text-gray-700">Metric Name</label>
                    <input 
                        type="text" 
                        id="metric_name" 
                        wire:model="metric_name" 
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('metric_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="warning_threshold" class="block text-sm font-medium text-gray-700">Warning Threshold</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        id="warning_threshold" 
                        wire:model="warning_threshold" 
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('warning_threshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="critical_threshold" class="block text-sm font-medium text-gray-700">Critical Threshold</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        id="critical_threshold" 
                        wire:model="critical_threshold" 
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('critical_threshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex space-x-3">
                    <button 
                        wire:click="saveThreshold"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        {{ $editingThresholdId ? 'Update' : 'Create' }}
                    </button>
                    <button 
                        wire:click="cancelEditing"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Thresholds List -->
    <div class="overflow-x-auto">
        @if (count($thresholds) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warning Threshold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Critical Threshold</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($thresholds as $threshold)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $threshold['metric_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $threshold['warning_threshold'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $threshold['critical_threshold'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button 
                                    wire:click="startEditing({{ $threshold['id'] }})"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                >
                                    Edit
                                </button>
                                <button 
                                    wire:click="deleteThreshold({{ $threshold['id'] }})"
                                    class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('Are you sure you want to delete this threshold?');"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-4 text-gray-500">
                No threshold configurations found. Add one to get started.
            </div>
        @endif
    </div>
</div>
