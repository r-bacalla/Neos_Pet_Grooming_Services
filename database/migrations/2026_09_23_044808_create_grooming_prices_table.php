<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_prices', function (Blueprint $table) {
            $table->id();

            $table->decimal('min_weight', 8, 2);
            $table->decimal('max_weight', 8, 2)->nullable();

            $table->decimal('price', 10, 2);

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['min_weight', 'max_weight']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grooming_prices');
    }
};