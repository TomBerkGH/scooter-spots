<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_location_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('osm_place_id')->nullable();
            $table->unsignedBigInteger('osm_id')->nullable();
            $table->string('osm_type')->nullable();
            $table->string('osm_class')->nullable();
            $table->string('place_type')->nullable();
            $table->unsignedSmallInteger('place_rank')->nullable();
            $table->decimal('importance', 12, 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('display_name')->nullable();
            $table->string('name')->nullable();
            $table->string('house_number')->nullable();
            $table->string('road')->nullable();
            $table->string('neighbourhood')->nullable();
            $table->string('suburb')->nullable();
            $table->string('city_district')->nullable();
            $table->string('city')->nullable();
            $table->string('town')->nullable();
            $table->string('village')->nullable();
            $table->string('municipality')->nullable();
            $table->string('county')->nullable();
            $table->string('state_district')->nullable();
            $table->string('state')->nullable();
            $table->string('region')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->json('bounding_box')->nullable();
            $table->json('address')->nullable();
            $table->json('extra_tags')->nullable();
            $table->json('name_details')->nullable();
            $table->json('geometry')->nullable();
            $table->json('raw_response');
            $table->text('license')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_location_data');
    }
};
