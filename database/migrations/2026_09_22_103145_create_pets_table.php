<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('species')->nullable();
            $table->string('breed')->nullable();
            $table->string('sex')->nullable();
            $table->date('birth_date')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};