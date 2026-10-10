<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_reference', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('inventory_quantity')->default(0);
            $table->unsignedInteger('available_quantity')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'expiry_date']);
        });

        DB::table('products')->orderBy('id')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                $inventoryQuantity = (int) $product->stock;
                $availableQuantity = (int) $product->available_stock;

                if ($inventoryQuantity === 0 && $availableQuantity === 0) {
                    continue;
                }

                DB::table('product_stock_batches')->insert([
                    'product_id' => $product->id,
                    'batch_reference' => 'LEGACY-' . $product->sku,
                    'expiry_date' => $product->expiry_date,
                    'inventory_quantity' => $inventoryQuantity,
                    'available_quantity' => $availableQuantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_batches');
    }
};