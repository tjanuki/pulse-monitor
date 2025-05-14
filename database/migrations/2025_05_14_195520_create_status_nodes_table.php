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
        Schema::create('status_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('environment');
            $table->string('region');
            $table->string('api_key')->unique();
            $table->string('status')->default('unknown');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_nodes');
    }
};
