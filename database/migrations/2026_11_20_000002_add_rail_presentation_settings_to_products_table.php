<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('rail_hanger_style', 16)->default('hook_only');
            $table->unsignedTinyInteger('rail_image_overlap')->default(12);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rail_hanger_style', 'rail_image_overlap']);
        });
    }
};
