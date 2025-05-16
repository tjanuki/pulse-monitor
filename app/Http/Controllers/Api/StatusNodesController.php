<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusNode;
use App\Models\StatusNodeRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StatusNodesController extends Controller
{
    /**
     * Display a listing of the status nodes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $nodes = StatusNode::with('latestMetrics')->get();
        
        return response()->json([
            'nodes' => $nodes,
        ]);
    }

    /**
     * Display the specified status node with its metrics.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $node = StatusNode::with('metrics')->find($id);
        
        if (!$node) {
            return response()->json([
                'error' => 'Status node not found',
            ], 404);
        }
        
        return response()->json([
            'node' => $node,
        ]);
    }

    /**
     * Update the specified status node.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $node = StatusNode::find($id);
        
        if (!$node) {
            return response()->json([
                'error' => 'Status node not found',
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'environment' => 'sometimes|required|string|max:255',
            'region' => 'sometimes|required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }
        
        try {
            $node->update($request->only(['name', 'environment', 'region']));
            
            return response()->json([
                'message' => 'Status node updated successfully',
                'node' => $node,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update status node: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to update status node',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified status node from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $node = StatusNode::find($id);
        
        if (!$node) {
            return response()->json([
                'error' => 'Status node not found',
            ], 404);
        }
        
        try {
            // Delete related metrics first
            $node->metrics()->delete();
            $node->delete();
            
            return response()->json([
                'message' => 'Status node deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete status node: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to delete status node',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate API key for the specified status node.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function regenerateApiKey($id)
    {
        $node = StatusNode::find($id);
        
        if (!$node) {
            return response()->json([
                'error' => 'Status node not found',
            ], 404);
        }
        
        try {
            $apiKey = StatusNodeRegistration::generateApiKey();
            
            $node->update(['api_key' => $apiKey]);
            
            return response()->json([
                'message' => 'API key regenerated successfully',
                'api_key' => $apiKey,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to regenerate API key: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to regenerate API key',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}