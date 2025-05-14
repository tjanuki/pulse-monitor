<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\StatusNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyNodeApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required',
            ], 401);
        }

        $statusNode = StatusNode::where('api_key', $apiKey)->first();

        if (!$statusNode) {
            Log::warning('Invalid API key used: ' . $apiKey);
            return response()->json([
                'error' => 'Invalid API key',
            ], 401);
        }

        // Update the last_seen_at timestamp
        $statusNode->update(['last_seen_at' => now()]);
        
        // Add the status node to the request for later use
        $request->merge(['status_node' => $statusNode]);

        return $next($request);
    }
}
