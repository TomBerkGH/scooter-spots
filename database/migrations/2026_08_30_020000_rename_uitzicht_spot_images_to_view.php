<?php

use App\Enums\SpotImageType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('spot_images')
            ->where('type', 'uitzicht')
            ->update([
                'type' => SpotImageType::View->value,
                'updated_at' => now(),
            ]);
    }
};
