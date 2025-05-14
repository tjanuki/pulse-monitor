<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThresholdConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'warning_threshold',
        'critical_threshold',
    ];

    protected $casts = [
        'warning_threshold' => 'float',
        'critical_threshold' => 'float',
    ];
}
