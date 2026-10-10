<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action');

            $table->string('module')->nullable();

            $table->string('record_type')->nullable();

            $table->unsignedBigInteger('record_id')->nullable();

            $table->text('description')->nullable();

            $table->json('old_values')->nullable();

            $table->json('new_values')->nullable();

            $table->string('ip_address')->nullable();

            $table->timestamps();

            $table->index('action');

            $table->index('module');

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};