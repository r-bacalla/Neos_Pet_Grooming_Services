<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('species')->default('Dog')->after('name');
        });

        Schema::table('grooming_addons', function (Blueprint $table) {
            $table->string('category')->default('extra_service')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('grooming_addons', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('species');
        });
    }
};