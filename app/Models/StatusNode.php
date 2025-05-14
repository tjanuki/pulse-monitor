<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'environment',
        'region',
        'api_key',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the metrics associated with this status node.
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(StatusMetric::class);
    }
}
