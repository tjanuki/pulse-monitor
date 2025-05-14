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
        Schema::create('status_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_node_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('group')->nullable();
            $table->float('value');
            $table->string('status')->default('normal');
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_metrics');
    }
};
