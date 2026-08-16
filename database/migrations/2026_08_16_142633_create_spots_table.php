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
        Schema::create('spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // GPS Coördinaten
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Media opslag pad (Cloudflare R2 of lokaal)
            $table->string('image_path');

            $table->timestamps();

            // Index voor snelle spatial / locatie-queries
            $table->index(['latitude', 'longitude']);
        });
    }
};
