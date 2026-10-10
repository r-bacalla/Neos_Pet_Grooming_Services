<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->decimal('actual_weight', 8, 2)
                ->nullable()
                ->after('notes');

            $table->decimal('grooming_price', 10, 2)
                ->nullable()
                ->after('actual_weight');

            $table->timestamp('approved_at')
                ->nullable()
                ->after('checked_in_at');

            $table->timestamp('received_at')
                ->nullable()
                ->after('approved_at');

            $table->timestamp('ready_at')
                ->nullable()
                ->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->dropColumn([
                'actual_weight',
                'grooming_price',
                'approved_at',
                'received_at',
                'ready_at',
            ]);
        });
    }
};