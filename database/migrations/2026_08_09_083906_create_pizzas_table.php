<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pizzas', function (Blueprint $table) {
            $table->id();
            $table->enum('size', ['sm', 'md', 'lg']);
            $table->enum('type', ['preset', 'custom']);
            $table->foreignId('preset_id')->nullable()->constrained('pizza_presets', 'id');
            $table->json('topping_codes')->default('[]');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pizzas');
    }
};
