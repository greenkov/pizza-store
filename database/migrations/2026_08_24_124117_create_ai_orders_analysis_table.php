<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_orders_analysis', function (Blueprint $table) {
            $table->id();
            $table->text('report');
            $table->json('presets_recommendations');
            $table->json('new_hot_ids');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_orders_analysis');
    }
};
