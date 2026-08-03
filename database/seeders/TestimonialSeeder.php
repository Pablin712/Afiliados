<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name_es' => 'Alumno AET #1',
                'name_en' => 'AET Student #1',
                'quote_es' => 'Me ayudó a estructurar mi operativa y dejar de improvisar.',
                'quote_en' => 'It helped me structure my trading and stop improvising.',
                'photo_path' => 'testimonios/testimonio1.jpeg',
            ],
            [
                'name_es' => 'Alumno AET #2',
                'name_en' => 'AET Student #2',
                'quote_es' => 'Ahora entiendo mejor el riesgo y cómo proteger mi capital.',
                'quote_en' => 'I now understand risk much better and how to protect my capital.',
                'photo_path' => 'testimonios/testimonio2.jpeg',
            ],
            [
                'name_es' => 'Alumno AET #3',
                'name_en' => 'AET Student #3',
                'quote_es' => 'El acompañamiento del equipo marcó la diferencia en mi avance.',
                'quote_en' => 'The team support made a big difference in my progress.',
                'photo_path' => 'testimonios/testimonio3.jpeg',
            ],
            [
                'name_es' => 'Alumno AET #4',
                'name_en' => 'AET Student #4',
                'quote_es' => 'Pasé de dudas constantes a tener un plan claro de ejecución.',
                'quote_en' => 'I moved from constant doubt to a clear execution plan.',
                'photo_path' => 'testimonios/testimonio4.jpeg',
            ],
        ];

        foreach ($testimonials as $index => $data) {
            Testimonial::query()->updateOrCreate(
                ['photo_path' => $data['photo_path']],
                [
                    'name_es' => $data['name_es'],
                    'name_en' => $data['name_en'],
                    'quote_es' => $data['quote_es'],
                    'quote_en' => $data['quote_en'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
