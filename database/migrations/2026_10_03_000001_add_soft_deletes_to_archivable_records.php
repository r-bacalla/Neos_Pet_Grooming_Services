<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'categories',
            'customers',
            'pets',
            'products',
            'product_stock_batches',
            'services',
            'grooming_prices',
            'users',
            'generated_reports',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'categories',
            'customers',
            'pets',
            'products',
            'product_stock_batches',
            'services',
            'grooming_prices',
            'users',
            'generated_reports',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};