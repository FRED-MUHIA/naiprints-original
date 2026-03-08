<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence')->unique();
            $table->string('number')->unique();
            $table->uuid('public_id')->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid');
            $table->string('currency', 10)->default('KES');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 6, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
