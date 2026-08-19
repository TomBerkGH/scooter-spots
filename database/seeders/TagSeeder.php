<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Zitbankje',
            'Picknickplek',
            'Uitzichtpunt',
            'Lunchroom',
            'Restaurant',
            'Café',
            'Koffiebar',
            'IJssalon',
            'Bakkerij',
            'Terras',
            'Boerderijwinkel',
            'Natuurgebied',
            'Park',
            'Bos',
            'Heide',
            'Strand',
            'Aan het water',
            'Historische plek',
            'Kasteel',
            'Molen',
            'Museum',
            'Tuin',
            'Markt',
            'Speeltuin',
            'Rustige plek',
            'Gratis toegankelijk',
            'Gratis parkeren',
            'Toilet aanwezig',
        ];

        foreach ($tags as $name) {
            Tag::query()->firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)],
            );
        }
    }
}
