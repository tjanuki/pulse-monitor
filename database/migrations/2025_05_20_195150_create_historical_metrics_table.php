<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historical_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_node_id')->constrained('status_nodes')->cascadeOnDelete();
            $table->string('metric_name');
            $table->string('group')->nullable();
            $table->float('min_value');
            $table->float('max_value');
            $table->float('avg_value');
            $table->string('period_type'); // 'hourly', 'daily', 'weekly', 'monthly'
            $table->timestamp('period_start');
            $table->timestamp('period_end'); 
            $table->integer('data_points_count');
            $table->timestamps();
            
            $table->index(['status_node_id', 'metric_name', 'period_type', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_metrics');
    }
};
