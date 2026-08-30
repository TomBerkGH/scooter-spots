<?php

use App\Enums\SpotImageType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('spots')
            ->select(['id', 'image_path'])
            ->orderBy('id')
            ->each(function (object $spot): void {
                DB::table('spot_images')->updateOrInsert(
                    [
                        'spot_id' => $spot->id,
                        'type' => SpotImageType::Main->value,
                    ],
                    [
                        'path' => $spot->image_path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });

        Schema::table('spots', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
