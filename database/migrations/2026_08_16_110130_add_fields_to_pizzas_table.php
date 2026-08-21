<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pizzas', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0);
            $table->string('name')->nullable()->after('preset_id')->default(null);

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('pizzas', function (Blueprint $table) {
            $table->dropColumn(['price', 'name']);
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
