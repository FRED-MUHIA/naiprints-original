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
        Schema::create('blog_posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->nullable()->constrained('blogs')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posters');
    }
};

