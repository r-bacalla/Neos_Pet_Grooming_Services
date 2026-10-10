<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->json('add_on_ids')->nullable()->after('add_on_product_ids');
        });
    }

    public function down(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->dropColumn('add_on_ids');
        });
    }
};