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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('iso2', 2)->nullable()->unique();
            $table->string('name')->unique();
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            $table->index(['active', 'display_order', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
