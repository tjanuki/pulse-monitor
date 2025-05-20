<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\RecommendationFactory;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger_metric',
        'condition',
        'threshold_value',
        'title',
        'description',
        'solution',
        'additional_info',
        'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'float',
        'additional_info' => 'array',
        'is_active' => 'boolean',
    ];

    public function matchesMetric(string $metricName, float $value): bool
    {
        if ($this->trigger_metric !== $metricName) {
            return false;
        }

        return match ($this->condition) {
            'above' => $value > $this->threshold_value,
            'below' => $value < $this->threshold_value,
            'equals' => $value == $this->threshold_value,
            default => false,
        };
    }
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return RecommendationFactory::new();
    }
}
