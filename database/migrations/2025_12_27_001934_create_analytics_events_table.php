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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            $table->string('event'); // page_view, whatsapp_click ...
            $table->string('entity_type')->nullable(); // page, product, user
            $table->string('entity_id')->nullable();

            $table->string('page', 512)->nullable();
            $table->string('source')->nullable();

            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();

            $table->index('event');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
