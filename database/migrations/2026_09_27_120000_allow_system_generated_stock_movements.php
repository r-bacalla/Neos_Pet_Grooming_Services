<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('stock_movements')->whereNull('user_id')->exists()) {
            $userId = DB::table('users')->where('role', 'admin')->value('id')
                ?? DB::table('users')->value('id');

            if (!$userId) {
                throw new RuntimeException('A user is required to restore non-null stock movement actors.');
            }

            DB::table('stock_movements')->whereNull('user_id')->update(['user_id' => $userId]);
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }
};