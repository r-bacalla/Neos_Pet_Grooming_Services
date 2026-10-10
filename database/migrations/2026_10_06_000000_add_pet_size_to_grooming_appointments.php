<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->string('pet_size')->nullable()->after('pet_id');
        });
    }

    public function down(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->dropColumn('pet_size');
        });
    }
};