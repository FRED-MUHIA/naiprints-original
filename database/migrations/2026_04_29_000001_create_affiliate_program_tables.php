<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_settings', function (Blueprint $table) {
            $table->id();
            $table->string('commission_type')->default('percentage');
            $table->decimal('commission_value', 12, 2)->default(10);
            $table->decimal('minimum_payout_amount', 12, 2)->default(1000);
            $table->timestamps();
        });

        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('payment_method');
            $table->text('payment_details');
            $table->string('status')->default('pending');
            $table->string('referral_code')->nullable()->unique();
            $table->string('commission_type')->nullable();
            $table->decimal('commission_value', 12, 2)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->string('referral_code');
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referral_source')->nullable();
            $table->text('landing_url')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'created_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->foreignId('affiliate_click_id')->nullable()->after('affiliate_id')->constrained('affiliate_clicks')->nullOnDelete();
            $table->string('affiliate_code')->nullable()->after('affiliate_click_id');
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('order_total', 12, 2)->default(0);
            $table->string('commission_type');
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
        });

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->text('payment_details');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_commissions');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('affiliate_click_id');
            $table->dropConstrainedForeignId('affiliate_id');
            $table->dropColumn('affiliate_code');
        });

        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('affiliate_settings');
    }
};
