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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('requirement')->nullable();
            $table->text('task_details');
            $table->string('assigned_to')->nullable();
            $table->dateTime('assigned_at');
            $table->string('status')->default('assigned');
            $table->unsignedTinyInteger('total_stages')->default(3);
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->string('stage_label')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
