<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_node_id',
        'name',
        'group',
        'value',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'float',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the node that owns the metric.
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(StatusNode::class, 'status_node_id');
    }
}
