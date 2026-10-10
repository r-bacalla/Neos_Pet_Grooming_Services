<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('sku')->unique();

            $table->string('category')->nullable();

            $table->decimal('cost', 12, 2)->default(0);

            $table->decimal('price', 12, 2);

            $table->unsignedInteger('stock')->default(0);

            $table->unsignedInteger('low_stock_level')->default(5);

            $table->date('expiry_date')->nullable();

            $table->decimal('expiry_discount', 5, 2)->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index('expiry_date');

            $table->index('stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};