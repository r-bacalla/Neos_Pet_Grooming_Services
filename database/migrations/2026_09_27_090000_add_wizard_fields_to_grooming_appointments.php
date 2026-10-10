<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->json('add_on_service_ids')->nullable()->after('service_id');
            $table->timestamp('waiver_confirmed_at')->nullable()->after('notes');
            $table->string('waiver_photo_path')->nullable()->after('waiver_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('grooming_appointments', function (Blueprint $table) {
            $table->dropColumn([
                'add_on_service_ids',
                'waiver_confirmed_at',
                'waiver_photo_path',
            ]);
        });
    }
};