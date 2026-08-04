<?php

namespace Database\Seeders;

use App\Models\HeroButton;
use App\Models\LandingContent;
use Illuminate\Database\Seeder;

class HeroButtonSeeder extends Seeder
{
    public function run(): void
    {
        $buttons = [
            [
                'label_es' => 'Ver programas',
                'label_en' => 'View programs',
                'url' => '#programas',
                'style' => 'primary',
            ],
            [
                'label_es' => 'Hablar con un asesor',
                'label_en' => 'Talk to an advisor',
                'url' => '#contacto',
                'style' => 'secondary',
            ],
            [
                'label_es' => 'Crea tu cuenta en Deriv',
                'label_en' => 'Create your Deriv account',
                'url' => 'https://deriv.partners/rx?sidc=7DC463EF-A2B7-4084-AC3B-25D715906684&utm_campaign=dynamicworks&utm_medium=affiliate&utm_source=CU304085',
                'style' => 'dark',
            ],
            [
                'label_es' => 'Crea tu cuenta en Weltrade',
                'label_en' => 'Create your Weltrade account',
                'url' => 'https://es.gowt.net/ib61404',
                'style' => 'accent',
            ],
        ];

        foreach ($buttons as $index => $data) {
            HeroButton::query()->updateOrCreate(
                ['url' => $data['url']],
                [
                    'label_es' => $data['label_es'],
                    'label_en' => $data['label_en'],
                    'style' => $data['style'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        // These buttons now live entirely in hero_buttons — remove the old
        // LandingContent rows so there's only one place to edit each one
        // (having both meant editing the text field silently did nothing).
        LandingContent::query()->whereIn('key', [
            'button_programs',
            'button_advisor',
            'button_deriv_account',
            'button_weltrade_account',
            'hero_deriv_url',
            'hero_weltrade_url',
        ])->delete();
    }
}
