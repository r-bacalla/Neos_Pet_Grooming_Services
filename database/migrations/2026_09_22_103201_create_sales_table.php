<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('subtotal', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('vat_rate', 5, 2)->default(12);

            $table->decimal('vat_amount', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);

            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->decimal('change_amount', 12, 2)->default(0);

            $table->string('payment_method')->nullable();

            $table->string('payment_reference')->nullable();

            $table->string('status')->default('completed');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('invoice_number');

            $table->index('payment_method');

            $table->index('status');

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};