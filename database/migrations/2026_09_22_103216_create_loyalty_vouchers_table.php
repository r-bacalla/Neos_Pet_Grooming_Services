<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_vouchers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->unique();

            $table->decimal('discount_percent', 5, 2);

            $table->unsignedInteger('required_visits');

            $table->date('issued_date');

            $table->date('expires_at');

            $table->string('status')->default('active');

            $table->foreignId('used_sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->timestamp('used_at')->nullable();

            $table->timestamps();

            $table->index('code');

            $table->index('status');

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_vouchers');
    }
};