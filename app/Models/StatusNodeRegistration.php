<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusNodeRegistration extends Model
{
    use HasFactory;

    /**
     * Register a new status node and generate an API key.
     *
     * @param string $name
     * @param string $environment
     * @param string $region
     * @return StatusNode
     */
    public static function register(string $name, string $environment, string $region): StatusNode
    {
        // Generate a unique API key
        $apiKey = self::generateApiKey();

        // Create the status node
        return StatusNode::create([
            'name' => $name,
            'environment' => $environment,
            'region' => $region,
            'api_key' => $apiKey,
            'status' => 'unknown',
        ]);
    }

    /**
     * Generate a unique API key.
     *
     * @return string
     */
    public static function generateApiKey(): string
    {
        $apiKey = Str::random(32);

        // Ensure the API key is unique
        while (StatusNode::where('api_key', $apiKey)->exists()) {
            $apiKey = Str::random(32);
        }

        return $apiKey;
    }
}
