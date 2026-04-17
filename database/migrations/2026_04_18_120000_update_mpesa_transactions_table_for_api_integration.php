<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mpesa_transactions')) {
            return;
        }

        $hasCheckoutRequestId = Schema::hasColumn('mpesa_transactions', 'checkout_request_id');
        $hasMerchantRequestId = Schema::hasColumn('mpesa_transactions', 'merchant_request_id');
        $hasPhone = Schema::hasColumn('mpesa_transactions', 'phone');
        $hasAmount = Schema::hasColumn('mpesa_transactions', 'amount');
        $hasStatus = Schema::hasColumn('mpesa_transactions', 'status');
        $hasReference = Schema::hasColumn('mpesa_transactions', 'reference');

        if (
            $hasCheckoutRequestId
            && $hasMerchantRequestId
            && $hasPhone
            && $hasAmount
            && $hasStatus
            && $hasReference
        ) {
            return;
        }

        Schema::table('mpesa_transactions', function (Blueprint $table) use (
            $hasCheckoutRequestId,
            $hasMerchantRequestId,
            $hasPhone,
            $hasAmount,
            $hasStatus,
            $hasReference
        ) {
            if (!$hasCheckoutRequestId) {
                $table->string('checkout_request_id')->nullable()->index();
            }

            if (!$hasMerchantRequestId) {
                $table->string('merchant_request_id')->nullable();
            }

            if (!$hasPhone) {
                $table->string('phone')->nullable();
            }

            if (!$hasAmount) {
                $table->decimal('amount', 12, 2)->nullable();
            }

            if (!$hasStatus) {
                $table->string('status')->default('pending');
            }

            if (!$hasReference) {
                $table->string('reference')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep existing production data intact.
    }
};
