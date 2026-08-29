<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_usages', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name');
            $table->string('conversation_uuid');
            $table->unsignedInteger('input_tokens');
            $table->unsignedInteger('output_tokens');
            $table->unsignedInteger('total_tokens');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_usages');
    }
};
