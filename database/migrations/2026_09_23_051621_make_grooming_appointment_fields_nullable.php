<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {

            $table->foreignId('service_id')
                ->nullable()
                ->change();

            $table->foreignId('groomer_id')
                ->nullable()
                ->change();

            $table->foreignId('sale_id')
                ->nullable()
                ->change();

            $table->dateTime('scheduled_start')
                ->nullable()
                ->change();

            $table->dateTime('scheduled_end')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {

            $table->foreignId('service_id')
                ->nullable(false)
                ->change();

            $table->foreignId('groomer_id')
                ->nullable(false)
                ->change();

            $table->foreignId('sale_id')
                ->nullable(false)
                ->change();

            $table->dateTime('scheduled_start')
                ->nullable(false)
                ->change();

            $table->dateTime('scheduled_end')
                ->nullable(false)
                ->change();
        });
    }
};