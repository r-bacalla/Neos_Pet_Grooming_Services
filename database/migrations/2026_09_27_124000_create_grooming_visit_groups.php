<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_visit_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->foreignId('visit_group_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('grooming_visit_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_group_id');
        });

        Schema::dropIfExists('grooming_visit_groups');
    }
};