<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('last_stock_in_quantity')->nullable()->after('stock');
            $table->timestamp('last_stock_in_at')->nullable()->after('last_stock_in_quantity');
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->string('weight_category')->nullable()->after('weight');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('reason');
            $table->index(['product_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'type', 'created_at']);
            $table->dropColumn('archived_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'last_stock_in_quantity',
                'last_stock_in_at',
            ]);
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn('weight_category');
        });
    }
};
