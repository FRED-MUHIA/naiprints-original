<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoice_items', 'product_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('invoice_id')->constrained()->nullOnDelete();
            });
        } elseif (! $this->hasForeignKey('invoice_items', 'product_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            });
        }

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            // MySQL requires an index for the invoice_id foreign key. Add a
            // non-unique replacement before removing the original unique key.
            if (! Schema::hasIndex('affiliate_commissions', 'affiliate_commissions_invoice_id_index')) {
                $table->index('invoice_id', 'affiliate_commissions_invoice_id_index');
            }

            if (Schema::hasIndex('affiliate_commissions', 'affiliate_commissions_invoice_id_unique', 'unique')) {
                $table->dropUnique('affiliate_commissions_invoice_id_unique');
            }

            if (! Schema::hasColumn('affiliate_commissions', 'invoice_item_id')) {
                $table->foreignId('invoice_item_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('affiliate_commissions', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('invoice_item_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasIndex('affiliate_commissions', 'affiliate_commissions_invoice_item_id_unique', 'unique')) {
                $table->unique('invoice_item_id');
            }
        });

        foreach (['invoice_item_id', 'product_id'] as $column) {
            if (! $this->hasForeignKey('affiliate_commissions', $column)) {
                Schema::table('affiliate_commissions', function (Blueprint $table) use ($column) {
                    $foreign = $table->foreign($column);
                    $foreign->references('id')->on($column === 'invoice_item_id' ? 'invoice_items' : 'products');

                    if ($column === 'invoice_item_id') {
                        $foreign->cascadeOnDelete();
                    } else {
                        $foreign->nullOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            if (Schema::hasIndex('affiliate_commissions', 'affiliate_commissions_invoice_item_id_unique', 'unique')) {
                $table->dropUnique('affiliate_commissions_invoice_item_id_unique');
            }
        });

        foreach (['product_id', 'invoice_item_id'] as $column) {
            if ($this->hasForeignKey('affiliate_commissions', $column)) {
                Schema::table('affiliate_commissions', function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            }

            if (Schema::hasColumn('affiliate_commissions', $column)) {
                Schema::table('affiliate_commissions', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        if (! Schema::hasIndex('affiliate_commissions', 'affiliate_commissions_invoice_id_unique', 'unique')) {
            Schema::table('affiliate_commissions', function (Blueprint $table) {
                $table->unique('invoice_id');
            });
        }

        if ($this->hasForeignKey('invoice_items', 'product_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        if (Schema::hasColumn('invoice_items', 'product_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        return collect(Schema::getForeignKeys($table))->contains(
            fn (array $foreignKey) => in_array($column, $foreignKey['columns'] ?? [], true)
        );
    }
};
