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
Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // key مثل: site_name, seo.meta_title, branding.logo
            $table->string('key')->unique();

            // نخزن القيمة كنص (حتى لو لون/رابط/JSON...)
            $table->longText('value')->nullable();

            // string | text | image | color | boolean | json | number ...
            $table->string('type')->default('string');

            // general | branding | seo | contact | about ...
            $table->string('group')->default('general')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
