<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stock_batches', function (Blueprint $table) {
            $table->timestamp('received_at')->nullable()->after('supplier');
        });
    }

    public function down(): void
    {
        Schema::table('product_stock_batches', function (Blueprint $table) {
            $table->dropColumn('received_at');
        });
    }
};