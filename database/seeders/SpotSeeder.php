<?php

namespace Database\Seeders;

use App\Models\Spot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SpotSeeder extends Seeder
{
    public function run(): void
    {
        $spots = [
            'tom@scooterspots.nl' => [
                [
                    'title' => 'Uitzicht over de Lek',
                    'description' => 'Rustige plek aan het water met een mooi uitzicht.',
                    'latitude' => 51.966180,
                    'longitude' => 5.047420,
                ],
                [
                    'title' => 'Bankje bij de Loosdrechtse Plassen',
                    'description' => 'Fijne tussenstop tijdens een rondje met de scooter.',
                    'latitude' => 52.206520,
                    'longitude' => 5.071450,
                ],
            ],
            'loes@scooterspots.nl' => [
                [
                    'title' => 'Kasteel De Haar',
                    'description' => 'Mooie plek voor een wandeling en een kop koffie.',
                    'latitude' => 52.121390,
                    'longitude' => 4.986020,
                ],
                [
                    'title' => 'De Munt in Utrecht',
                    'description' => 'Leuke plek aan het Merwedekanaal.',
                    'latitude' => 52.089030,
                    'longitude' => 5.095540,
                ],
            ],
        ];

        foreach ($spots as $email => $userSpots) {
            $user = User::query()->where('email', $email)->firstOrFail();

            foreach ($userSpots as $index => $data) {
                $imagePath = 'spots/demo-'.$user->id.'-'.$index.'.svg';
                Storage::disk('r2')->put($imagePath, $this->placeholder($data['title']));

                Spot::query()->updateOrCreate(
                    ['user_id' => $user->id, 'title' => $data['title']],
                    [...$data, 'image_path' => $imagePath],
                );
            }
        }
    }

    private function placeholder(string $title): string
    {
        $safeTitle = htmlspecialchars($title, ENT_XML1);

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">
                <rect width="1200" height="900" fill="#dbeafe"/>
                <circle cx="930" cy="190" r="90" fill="#fbbf24"/>
                <path d="M0 650 260 420 490 620 710 360 1200 690V900H0Z" fill="#65a30d"/>
                <path d="M0 730 Q300 650 600 740 T1200 710 V900 H0Z" fill="#38bdf8"/>
                <text x="600" y="820" text-anchor="middle" font-family="sans-serif" font-size="44" fill="#172554">$safeTitle</text>
            </svg>
            SVG;
    }
}
