<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->string('attachment_path')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('phone')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->string('tag')->nullable(); // sales | support | spam ...
            $table->timestamp('replied_at')->nullable();

            // Adding the new column 'project_type'
            $table->string('project_type')->nullable(); // The type of project (e.g., "development", "marketing", etc.)

            // Adding the 'services' column (for multiple services selection)
            $table->json('services')->nullable(); // The list of selected services
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
