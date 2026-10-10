<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stock_batches', function (Blueprint $table) {
            $table->string('supplier', 255)->nullable()->after('batch_reference');
        });
    }

    public function down(): void
    {
        Schema::table('product_stock_batches', function (Blueprint $table) {
            $table->dropColumn('supplier');
        });
    }
};