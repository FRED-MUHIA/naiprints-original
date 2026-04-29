<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('invoice_id')->constrained()->nullOnDelete();
        });

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropUnique('affiliate_commissions_invoice_id_unique');
            $table->foreignId('invoice_item_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->after('invoice_item_id')->constrained()->nullOnDelete();
            $table->unique('invoice_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropUnique('affiliate_commissions_invoice_item_id_unique');
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('invoice_item_id');
            $table->unique('invoice_id');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
