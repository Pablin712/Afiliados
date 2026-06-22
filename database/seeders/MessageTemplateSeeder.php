<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key'         => 'bienvenida',
                'name'        => 'Mensaje de bienvenida (registro gratuito)',
                'description' => 'Se envía cuando un usuario completa el registro gratuito. Variables disponibles: {name}, {email}, {phone}',
                'body'        => "¡Bienvenido/a {name} a AET Trader Academy! 👨🏻‍💻\n\nDesde este momento estaré acompañándote en tu proceso dentro del sistema.\nSoy Donna - asistente de AET Trader Academy.\n\nSi ya estás usando la versión gratuita, el siguiente paso es simple:\nentra ahora a nuestro canal de Telegram.\n\nhttps://t.me/aetsas\n\nAhí vas a encontrar los enlaces gratuitos y todo el contenido para que empieces correctamente.\n\nAdemás, te recomendamos crearte tu cuenta a través de los enlaces que tienes en la página principal de AET, para que puedas operar y aprovechar correctamente todas las herramientas.\n\nAhora, si realmente quieres resultados y no solo mirar, el plan premium de \$197 te desbloquea todo:\nCuenta de 50 usd para tradear\nescáner, módulos educativos, señales y clases en vivo.\n\nNo te quedes a medias. Avanza.\n\nTe recomiendo analizar en\nhttps://tradingview.deriv.com/\n\nY trabajar con cuentas standar mt5 que podrás aperturar dentro de los links de nuestros brokers asociados.",
            ],
            [
                'key'         => 'post_pago',
                'name'        => 'Mensaje post-pago (membresía activada)',
                'description' => 'Se envía cuando el pago de un usuario es aprobado y su membresía es activada. Variables disponibles: {name}, {email}, {phone}',
                'body'        => "¡Bienvenido/a a AET Trader Academy! 👨🏻‍💻\n\nDesde este momento estaré acompañándote en tu proceso dentro del sistema.\nSoy Donna - asistente de AET Trader Academy.\n\nAcceso a la comunidad\n\nCanal oficial\nhttps://t.me/+tv9B-1V8eWdhMjIx\n\nSeñales VIP\nhttps://t.me/+AoQTrGdNxhNlMWFh\nhttps://t.me/+tJuQIHiKP0JkYzYx\n\nGrupo Premium\nhttps://t.me/+jVdPcBcKNFAyYzZh\n\n\nSi también quieres generar ingresos recomendando el sistema, responde:\n\n\"Quiero estar en el sistema de referidos\"\n\ny agendamos un Zoom para explicarte cómo funciona.\n\nA partir de ahora estaré pendiente para ayudarte en tu proceso dentro de AET TRADER ACADEMY",
            ],
        ];

        foreach ($templates as $data) {
            MessageTemplate::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}
