<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_addon_service', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grooming_addon_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'grooming_addon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grooming_addon_service');
    }
};