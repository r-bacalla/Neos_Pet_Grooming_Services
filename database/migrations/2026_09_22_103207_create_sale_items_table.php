<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('item_type');

            $table->string('item_name');

            $table->decimal('unit_price', 12, 2);

            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('line_total', 12, 2);

            $table->timestamps();

            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};