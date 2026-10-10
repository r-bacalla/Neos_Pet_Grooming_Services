<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type');

            $table->integer('quantity');

            $table->unsignedInteger('stock_before');

            $table->unsignedInteger('stock_after');

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index('type');

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};