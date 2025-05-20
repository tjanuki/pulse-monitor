<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\AlertFactory;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_node_id',
        'status_metric_id',
        'type',
        'message',
        'context',
        'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function statusNode(): BelongsTo
    {
        return $this->belongsTo(StatusNode::class);
    }

    public function statusMetric(): BelongsTo
    {
        return $this->belongsTo(StatusMetric::class);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeCritical($query)
    {
        return $query->where('type', 'critical');
    }

    public function scopeWarning($query)
    {
        return $query->where('type', 'warning');
    }
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return AlertFactory::new();
    }
}
