<?php

namespace Database\Seeders;

use App\Models\LandingContent;
use Illuminate\Database\Seeder;

class LandingContentSeeder extends Seeder
{
    /**
     * Default content for the public welcome page, grouped for the admin UI.
     * Every key here mirrors the historical `messages.welcome.*` translation
     * suffix so the initial seed renders byte-identical to the old hardcoded
     * page. Bilingual fields carry an ['es' => ..., 'en' => ...] pair;
     * locale-independent fields (images, links, contact data) carry a
     * single ['all' => ...] value.
     */
    public function run(): void
    {
        $groups = [
            'hero' => [
                'section_badge' => ['type' => 'text', 'es' => 'Formación en Trading • Nivel inicial a avanzado', 'en' => 'Trading Training • Beginner to Advanced Levels'],
                'main_title' => ['type' => 'textarea', 'es' => 'Aprende trading con estructura, gestión de riesgo y acompañamiento real.', 'en' => 'Learn trading with structure, risk management and real support.'],
                'main_description' => ['type' => 'textarea', 'es' => 'En AET Trader Academy te guiamos paso a paso para construir una metodología sólida: análisis técnico, psicología del trader y ejecución disciplinada.', 'en' => 'At AET Trader Academy we guide you step by step to build a solid methodology: technical analysis, trader psychology and disciplined execution.'],
                'button_programs' => ['type' => 'text', 'es' => 'Ver programas', 'en' => 'View programs'],
                'button_advisor' => ['type' => 'text', 'es' => 'Hablar con un asesor', 'en' => 'Talk to an advisor'],
                'button_deriv_account' => ['type' => 'text', 'es' => 'Crea tu cuenta en Deriv', 'en' => 'Create your Deriv account'],
                'button_weltrade_account' => ['type' => 'text', 'es' => 'Crea tu cuenta en Weltrade', 'en' => 'Create your Weltrade account'],
                'trading_accounts_hint' => ['type' => 'textarea', 'es' => 'También puedes abrir tu cuenta de trading directamente desde aquí con nuestros enlaces oficiales.', 'en' => 'You can also open your trading account directly from here using our official links.'],
                'hero_logo_caption' => ['type' => 'textarea', 'es' => 'Educación en trading con una identidad clara, moderna y enfocada en resultados.', 'en' => 'Trading education with a clear, modern identity focused on results.'],
                'hero_image_light' => ['type' => 'image', 'all' => 'letras_cuadrado.jpeg'],
                'hero_image_dark' => ['type' => 'image', 'all' => 'logo.jpg'],
                'hero_deriv_url' => ['type' => 'text', 'all' => 'https://deriv.partners/rx?sidc=7DC463EF-A2B7-4084-AC3B-25D715906684&utm_campaign=dynamicworks&utm_medium=affiliate&utm_source=CU304085'],
                'hero_weltrade_url' => ['type' => 'text', 'all' => 'https://es.gowt.net/ib61404'],
            ],
            'programs' => [
                'programs_badge' => ['type' => 'text', 'es' => 'Programas AET', 'en' => 'AET programs'],
                'programs_title' => ['type' => 'textarea', 'es' => 'Una ruta clara para aprender, operar con criterio y avanzar con acompañamiento real.', 'en' => 'A clear path to learn, trade with criteria, and move forward with real support.'],
                'programs_description' => ['type' => 'textarea', 'es' => 'Programas más claros, directos y enfocados en lo que de verdad necesita un alumno para avanzar.', 'en' => 'Clearer, shorter programs focused on what students actually need to keep progressing.'],
                'programs_pill_1' => ['type' => 'text', 'es' => 'Análisis técnico con contexto', 'en' => 'Technical analysis with context'],
                'programs_pill_2' => ['type' => 'text', 'es' => 'Gestión del riesgo aplicable', 'en' => 'Practical risk management'],
                'programs_pill_3' => ['type' => 'text', 'es' => 'Psicología y disciplina', 'en' => 'Psychology and discipline'],
                'programs_pill_4' => ['type' => 'text', 'es' => 'Acompañamiento continuo', 'en' => 'Continuous guidance'],
                'program_card_1_eyebrow' => ['type' => 'text', 'es' => 'Ruta formativa', 'en' => 'Learning path'],
                'program_card_1_title' => ['type' => 'text', 'es' => 'Programa de formación estructurada', 'en' => 'Structured training program'],
                'program_card_1_desc' => ['type' => 'textarea', 'es' => 'Aprende con una ruta clara, reglas definidas y criterio operativo.', 'en' => 'Learn with a clear path, defined rules, and better trading criteria.'],
                'program_card_1_item_1_title' => ['type' => 'text', 'es' => 'Base técnica y lectura del mercado', 'en' => 'Technical foundation and market reading'],
                'program_card_1_item_2_title' => ['type' => 'text', 'es' => 'Planificación y gestión del riesgo', 'en' => 'Setup planning and risk management'],
                'program_card_1_item_3_title' => ['type' => 'text', 'es' => 'Ejecución con disciplina', 'en' => 'Execution follow-up and consistency'],
                'program_card_2_eyebrow' => ['type' => 'text', 'es' => 'Soporte', 'en' => 'Support ecosystem'],
                'program_card_2_title' => ['type' => 'text', 'es' => 'Beneficios que acompañan el proceso', 'en' => 'Benefits that reinforce the process'],
                'program_card_2_desc' => ['type' => 'textarea', 'es' => 'No es solo una clase: también cuentas con recursos y seguimiento.', 'en' => 'It is not just a class: you also get resources and follow-up support.'],
                'program_card_2_item_1_title' => ['type' => 'text', 'es' => 'Comunidad y contacto cercano', 'en' => 'Community and close contact'],
                'program_card_2_item_2_title' => ['type' => 'text', 'es' => 'Material y recursos de apoyo', 'en' => 'Materials and support resources'],
                'program_card_2_item_3_title' => ['type' => 'text', 'es' => 'Enfoque progresivo y sostenible', 'en' => 'Progressive and sustainable approach'],
                'programs_panel_badge' => ['type' => 'text', 'es' => 'Identidad original AET', 'en' => 'Original AET identity'],
                'programs_panel_title' => ['type' => 'textarea', 'es' => 'Una academia pensada para avanzar con método, disciplina y claridad.', 'en' => 'An academy built to move forward with method, discipline, and clarity.'],
                'programs_panel_description' => ['type' => 'textarea', 'es' => 'Te mostramos una propuesta clara, profesional y enfocada en resultados sostenibles.', 'en' => 'A clearer and more professional section focused on the value we actually deliver.'],
                'programs_stat_1_label' => ['type' => 'text', 'es' => 'Enfoque', 'en' => 'Focus'],
                'programs_stat_1_value' => ['type' => 'text', 'es' => 'Formación aplicable al mercado real', 'en' => 'Training that applies to real markets'],
                'programs_stat_2_label' => ['type' => 'text', 'es' => 'Prioridad', 'en' => 'Priority'],
                'programs_stat_2_value' => ['type' => 'text', 'es' => 'Gestión del riesgo antes que la emoción', 'en' => 'Risk management before emotion'],
                'programs_stat_3_label' => ['type' => 'text', 'es' => 'Meta', 'en' => 'Goal'],
                'programs_stat_3_value' => ['type' => 'text', 'es' => 'Construir consistencia paso a paso', 'en' => 'Build consistency step by step'],
                'programs_premium_schedule' => ['type' => 'textarea', 'es' => 'De lunes a jueves, 9:00 PM por Zoom. Unicamente para el grupo premium de AET.', 'en' => 'Monday to Thursday, 9:00 PM via Zoom. Exclusive for the AET premium group.'],
                'programs_cta' => ['type' => 'text', 'es' => 'Solicitar orientación personalizada', 'en' => 'Get personalized guidance'],
            ],
            'about' => [
                'behind_title' => ['type' => 'textarea', 'es' => 'Quién está detrás de todo esto', 'en' => 'Who is behind all this'],
                'behind_description' => ['type' => 'textarea', 'es' => 'AET Trader Academy nace para ayudar a personas a invertir su dinero con estructura, gestión del riesgo y disciplina. Detrás de este proyecto hay una visión clara: formar una comunidad que opere con criterio, no por impulso.', 'en' => 'AET Trader Academy was built to help people learn trading with structure, risk management, and discipline. Behind this project there is a clear mission: to build a community that trades with criteria, not impulse.'],
                'behind_focus_1_label' => ['type' => 'text', 'es' => 'Enfoque', 'en' => 'Focus'],
                'behind_focus_1_value' => ['type' => 'text', 'es' => 'Educación práctica y accionable', 'en' => 'Practical and actionable education'],
                'behind_focus_2_label' => ['type' => 'text', 'es' => 'Objetivo', 'en' => 'Goal'],
                'behind_focus_2_value' => ['type' => 'text', 'es' => 'Resultados sostenibles a corto, mediano y largo plazo', 'en' => 'Sustainable results in the short, medium, and long term'],
                'behind_badge' => ['type' => 'text', 'es' => 'Formación en trading con acompañamiento', 'en' => 'Trading education with guidance'],
                'behind_quote' => ['type' => 'textarea', 'es' => '"No se trata de adivinar el mercado: se trata de construir criterio, método y constancia."', 'en' => '"This is not about guessing the market; it is about building criteria, method, and consistency."'],
                'behind_photo' => ['type' => 'image', 'all' => 'me/esteban1.jpeg'],
                'behind_person_name' => ['type' => 'text', 'all' => 'Esteban Rivera'],
            ],
            'testimonials_section' => [
                'testimonials_title' => ['type' => 'text', 'es' => 'Testimonios', 'en' => 'Testimonials'],
                'testimonials_description' => ['type' => 'textarea', 'es' => 'Historias reales de alumnos que están fortaleciendo su proceso con AET Trader Academy.', 'en' => 'Real stories from students strengthening their process with AET Trader Academy.'],
            ],
            'contact' => [
                'contact_badge' => ['type' => 'text', 'es' => 'Contacta con nosotros', 'en' => 'Contact us'],
                'contact_title' => ['type' => 'textarea', 'es' => 'Elige la ruta correcta segun tu perfil.', 'en' => 'Choose the right path based on your profile.'],
                'contact_description' => ['type' => 'textarea', 'es' => 'Separamos el contacto en dos recorridos para que la informacion sea mas clara: si estas empezando, te guiamos desde cero; si ya eres trader, te mostramos como trabajar con nosotros.', 'en' => 'We split contact into two clear routes: if you are starting from zero, we guide you from the beginning; if you are already a trader, we show you how to work with us.'],
                'contact_tab_new' => ['type' => 'text', 'es' => 'Soy nuevo', 'en' => 'I am new'],
                'contact_tab_trader' => ['type' => 'text', 'es' => 'Ya soy trader', 'en' => 'I am already a trader'],
                'contact_new_eyebrow' => ['type' => 'text', 'es' => 'Para personas que no son traders', 'en' => 'For people who are not traders yet'],
                'contact_new_title' => ['type' => 'textarea', 'es' => 'Empieza con orientacion clara y sin complicarte.', 'en' => 'Start with clear guidance and no unnecessary friction.'],
                'contact_new_description' => ['type' => 'textarea', 'es' => 'Si aun no operas o estas dando tus primeros pasos, te ayudamos a entender por donde comenzar y cual es la mejor forma de acercarte al trading con criterio.', 'en' => 'If you are not trading yet or you are just getting started, we help you understand where to begin and what the best first step looks like.'],
                'contact_new_step_1' => ['type' => 'text', 'es' => 'Conocer tu punto de partida', 'en' => 'Understand your starting point'],
                'contact_new_step_2' => ['type' => 'text', 'es' => 'Explicarte la ruta de aprendizaje', 'en' => 'Explain the learning path'],
                'contact_new_step_3' => ['type' => 'text', 'es' => 'Dirigirte al canal correcto de apoyo', 'en' => 'Direct you to the right support channel'],
                'contact_trader_eyebrow' => ['type' => 'text', 'es' => 'Para traders con experiencia', 'en' => 'For experienced traders'],
                'contact_trader_title' => ['type' => 'textarea', 'es' => 'Si ya eres trader, conversemos para trabajar contigo.', 'en' => 'If you are already a trader, let us talk about working together.'],
                'contact_trader_description' => ['type' => 'textarea', 'es' => 'Este espacio esta pensado para traders que ya tienen recorrido y quieren conocer como pueden integrarse, colaborar o crecer junto a nuestro equipo.', 'en' => 'This space is meant for traders who already have experience and want to explore how they can integrate, collaborate, or grow together with our team.'],
                'contact_trader_item_1_title' => ['type' => 'text', 'es' => 'Perfil mas alineado', 'en' => 'Best aligned profile'],
                'contact_trader_item_1_desc' => ['type' => 'textarea', 'es' => 'Traders con experiencia operativa, criterio tecnico y disposicion para trabajar con estructura.', 'en' => 'Traders with operating experience, technical criteria, and willingness to work with structure.'],
                'contact_trader_item_2_title' => ['type' => 'text', 'es' => 'Que esperamos', 'en' => 'What we expect'],
                'contact_trader_item_2_desc' => ['type' => 'textarea', 'es' => 'Compromiso, comunicacion clara y una forma profesional de relacionarse con la academia y su comunidad.', 'en' => 'Commitment, clear communication, and a professional way of working with the academy and its community.'],
                'contact_trader_item_3_title' => ['type' => 'text', 'es' => 'Siguiente paso', 'en' => 'Next step'],
                'contact_trader_item_3_desc' => ['type' => 'textarea', 'es' => 'Escribenos y cuentanos tu perfil para revisar si existe encaje y coordinar una conversacion.', 'en' => 'Write to us and tell us about your profile so we can evaluate the fit and coordinate a conversation.'],
                'contact_trader_whatsapp_title' => ['type' => 'text', 'es' => 'Hablar por WhatsApp ahora', 'en' => 'Open WhatsApp now'],
                'contact_trader_whatsapp_desc' => ['type' => 'textarea', 'es' => 'Abrimos el chat con el mensaje listo para enviar.', 'en' => 'We open the chat with the message ready to send.'],
                'contact_trader_email_title' => ['type' => 'text', 'es' => 'Enviar correo profesional', 'en' => 'Send a professional email'],
                'contact_trader_side_badge' => ['type' => 'text', 'es' => 'Ruta trader', 'en' => 'Trader route'],
                'contact_trader_side_title' => ['type' => 'textarea', 'es' => 'Una conversacion directa, rapida y bien enfocada.', 'en' => 'A direct, fast, and focused conversation.'],
                'contact_trader_side_description' => ['type' => 'textarea', 'es' => 'Priorizamos que el contacto llegue con contexto. Por eso el acceso principal envia un mensaje ya redactado para acelerar la conversacion.', 'en' => 'We want the contact to arrive with context. That is why the main CTA opens a prewritten message to speed up the conversation.'],
                'contact_trader_ready_message_label' => ['type' => 'text', 'es' => 'Mensaje preparado', 'en' => 'Prepared message'],
                'contact_trader_ready_message' => ['type' => 'textarea', 'es' => 'Hola soy trader quiero trabajar con ustedes.', 'en' => 'Hola soy trader quiero trabajar con ustedes.'],
                'contact_qr_title' => ['type' => 'text', 'es' => 'Escanea y unete al canal de Telegram', 'en' => 'Scan and join the Telegram channel'],
                'contact_qr_description' => ['type' => 'textarea', 'es' => 'Tambien puedes tocar el boton para abrir el canal directamente.', 'en' => 'You can also tap the button to open the channel directly.'],
                'contact_qr_alt' => ['type' => 'text', 'es' => 'Codigo QR del canal de Telegram de AET Trader Academy', 'en' => 'Telegram channel QR code for AET Trader Academy'],
                'contact_qr_button' => ['type' => 'text', 'es' => 'Abrir canal en Telegram', 'en' => 'Open Telegram channel'],
                'contact_qr_image' => ['type' => 'image', 'all' => 'contact/telegram.jpeg'],
                'contact_telegram_url' => ['type' => 'text', 'all' => 'https://t.me/AETSAS'],
                'contact_telegram_handle_display' => ['type' => 'text', 'all' => '@AETSAS'],
                'contact_instagram_url' => ['type' => 'text', 'all' => 'https://www.instagram.com/aet.trader.academy?igsh=cWsxN21qM2o5bmg0&utm_source=qr'],
                'contact_instagram_handle_display' => ['type' => 'text', 'all' => '@aet.trader.academy'],
                'contact_email' => ['type' => 'text', 'all' => 'Aetsas01@gmail.com'],
                'contact_whatsapp_number_display' => ['type' => 'text', 'all' => '+593 97 885 5098'],
                'contact_whatsapp_number_raw' => ['type' => 'text', 'all' => '593978855098'],
            ],
            'footer' => [
                'footer_copyright_suffix' => ['type' => 'text', 'es' => 'AET Trader Academy. Todos los derechos reservados.', 'en' => 'AET Trader Academy. All rights reserved.'],
            ],
        ];

        foreach ($groups as $group => $fields) {
            $order = 0;

            foreach ($fields as $key => $definition) {
                $type = $definition['type'];
                $order++;

                if (array_key_exists('all', $definition)) {
                    $this->upsert($key, LandingContent::LOCALE_ALL, $type, $group, $order, $definition['all']);

                    continue;
                }

                $this->upsert($key, 'es', $type, $group, $order, $definition['es']);
                $this->upsert($key, 'en', $type, $group, $order, $definition['en']);
            }
        }
    }

    private function upsert(string $key, string $locale, string $type, string $group, int $order, ?string $value): void
    {
        LandingContent::query()->updateOrCreate(
            ['key' => $key, 'locale' => $locale],
            ['type' => $type, 'group' => $group, 'sort_order' => $order, 'value' => $value]
        );
    }
}
