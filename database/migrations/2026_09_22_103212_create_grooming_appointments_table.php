<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('groomer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->dateTime('scheduled_start');

            $table->dateTime('scheduled_end');

            $table->string('status')->default('scheduled');

            $table->text('notes')->nullable();

            $table->text('groomer_notes')->nullable();

            $table->timestamp('checked_in_at')->nullable();

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('scheduled_start');

            $table->index('status');

            $table->index('groomer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grooming_appointments');
    }
};