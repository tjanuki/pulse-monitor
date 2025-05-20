<?php

namespace App\Http\Controllers\Api;

use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationsController extends Controller
{
    /**
     * Get all recommendations
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $recommendations = Recommendation::all();
        
        return response()->json([
            'data' => $recommendations,
        ]);
    }

    /**
     * Get a specific recommendation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $recommendation = Recommendation::findOrFail($id);
        
        return response()->json([
            'data' => $recommendation,
        ]);
    }

    /**
     * Create a new recommendation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trigger_metric' => 'required|string',
            'condition' => 'required|string|in:above,below,equals',
            'threshold_value' => 'required|numeric',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'solution' => 'required|string',
            'additional_info' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $recommendation = Recommendation::create($validated);
        
        return response()->json([
            'message' => 'Recommendation created successfully',
            'data' => $recommendation,
        ], 201);
    }

    /**
     * Update a recommendation
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $recommendation = Recommendation::findOrFail($id);
        
        $validated = $request->validate([
            'trigger_metric' => 'string',
            'condition' => 'string|in:above,below,equals',
            'threshold_value' => 'numeric',
            'title' => 'string|max:255',
            'description' => 'string',
            'solution' => 'string',
            'additional_info' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $recommendation->update($validated);
        
        return response()->json([
            'message' => 'Recommendation updated successfully',
            'data' => $recommendation->fresh(),
        ]);
    }

    /**
     * Delete a recommendation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $recommendation = Recommendation::findOrFail($id);
        $recommendation->delete();
        
        return response()->json([
            'message' => 'Recommendation deleted successfully',
        ]);
    }

    /**
     * Toggle the active status of a recommendation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        $recommendation = Recommendation::findOrFail($id);
        $recommendation->is_active = !$recommendation->is_active;
        $recommendation->save();
        
        $status = $recommendation->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'message' => "Recommendation {$status} successfully",
            'data' => $recommendation,
        ]);
    }
}
