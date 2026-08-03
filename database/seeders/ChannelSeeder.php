<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Seeds the channels already in use by the app/n8n so nothing breaks
     * after switching to DB-driven channel administration.
     *
     * aet_premium (purpose = class_reminder_premium) always gets every class
     * reminder, general and exclusive — ClassScheduleReminderService queries
     * both purposes for non-exclusive classes. No duplicate row is needed.
     */
    public function run(): void
    {
        $botToken = (string) config('affiliates.telegram.bot_token', '');
        $aetPremiumChatId = (string) config('affiliates.telegram.groups.aet_premium', '');

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_TELEGRAM, 'name' => 'aet_premium'],
            [
                'purpose'      => Channel::PURPOSE_CLASS_REMINDER_PREMIUM,
                'is_active'    => true,
                'is_exclusive' => true,
                'chat_id'      => $aetPremiumChatId,
                'bot_token'    => $botToken,
                'notes'        => 'Grupo Telegram AET Premium (exclusivo). Recibe todos los recordatorios de clase (generales y exclusivos), y participa en la expulsión semanal de miembros free.',
            ]
        );

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_TELEGRAM, 'name' => 'aet_sas_2k26'],
            [
                'purpose'      => Channel::PURPOSE_CLASS_REMINDER_ALL,
                'is_active'    => true,
                'is_exclusive' => false,
                'chat_id'      => '-1003511484723',
                'bot_token'    => $botToken,
                'notes'        => 'Canal Telegram "AET SAS 2k26" (@aetsas) — recordatorios de clases para todos los miembros. Abierto a todos, nunca participa en la expulsión.',
            ]
        );

        $telegramGroups = [
            'aet_vip_deriv'    => (string) config('affiliates.telegram.groups.aet_vip_deriv', ''),
            'aet_vip_weltrade' => (string) config('affiliates.telegram.groups.aet_vip_weltrade', ''),
        ];

        foreach ($telegramGroups as $key => $chatId) {
            Channel::query()->updateOrCreate(
                ['type' => Channel::TYPE_TELEGRAM, 'name' => $key],
                [
                    'purpose'      => Channel::PURPOSE_GENERAL,
                    'is_active'    => true,
                    'is_exclusive' => true,
                    'chat_id'      => $chatId,
                    'bot_token'    => $botToken,
                    'notes'        => 'Importado automáticamente desde config/affiliates.php. Participa en la expulsión semanal de miembros free.',
                ]
            );
        }

        // Grupos VIP por índice sintético (mismo tipo/función que aet_vip_deriv /
        // aet_vip_weltrade). El nombre interno y el título reflejan el título
        // real del grupo en Telegram (verificado vía Bot API getChat).
        $syntheticIndexGroups = [
            'aet_vip_deriv_boom900'    => ['chat_id' => '-1003742276402', 'title' => 'AET VIP DERIV BOOM900'],
            'aet_vip_boom600'          => ['chat_id' => '-1004303646865', 'title' => 'AET VIP BOOM600'],
            'aet_vip_deriv_boom500'    => ['chat_id' => '-1003779440630', 'title' => 'AET VIP DERIV BOOM500'],
            'aet_vip_deriv_boom300'    => ['chat_id' => '-1003792234931', 'title' => 'AET VIP DERIV BOOM300'],
            'aet_vip_weltrade_crash1000' => ['chat_id' => '-1004340084613', 'title' => 'AET VIP WELTRADE CRASH1000'],
            'aet_vip_weltrade_crash900'  => ['chat_id' => '-1003763757799', 'title' => 'AET VIP WELTRADE CRASH900'],
            'aet_vip_weltrade_crash600'  => ['chat_id' => '-1004430056028', 'title' => 'AET VIP WELTRADE CRASH600'],
            'aet_vip_weltrade_crash500'  => ['chat_id' => '-1003697131429', 'title' => 'AET VIP WELTRADE CRASH500'],
            'aet_vip_weltrade_crash300'  => ['chat_id' => '-1004385644254', 'title' => 'AET VIP WELTRADE CRASH300'],
        ];

        foreach ($syntheticIndexGroups as $key => $group) {
            Channel::query()->updateOrCreate(
                ['type' => Channel::TYPE_TELEGRAM, 'name' => $key],
                [
                    'purpose'      => Channel::PURPOSE_GENERAL,
                    'is_active'    => true,
                    'is_exclusive' => true,
                    'chat_id'      => $group['chat_id'],
                    'bot_token'    => $botToken,
                    'notes'        => "Grupo Telegram \"{$group['title']}\" — señales de índice sintético. Participa en la expulsión semanal de miembros free.",
                ]
            );
        }

        // Grupo Telegram "AET SAS Señales" (chat_id -1003914721960, con Temas
        // habilitados). Cada índice sintético tendrá su propio tema dentro de
        // este mismo grupo en vez de un grupo separado; a medida que se creen
        // más temas, agregar una entrada por tema aquí y retirar el grupo
        // individual correspondiente (aet_vip_deriv_boomXXX, etc.).
        $forumTopicGroups = [
            'aet_senales_boom1000' => [
                'chat_id' => '-1003914721960',
                'message_thread_id' => 2,
                'title' => 'AET SAS Señales — tema Boom 1000',
            ],
        ];

        foreach ($forumTopicGroups as $key => $topic) {
            Channel::query()->updateOrCreate(
                ['type' => Channel::TYPE_TELEGRAM, 'name' => $key],
                [
                    'purpose'            => Channel::PURPOSE_GENERAL,
                    'is_active'          => true,
                    'is_exclusive'       => true,
                    'chat_id'            => $topic['chat_id'],
                    'message_thread_id'  => $topic['message_thread_id'],
                    'bot_token'          => $botToken,
                    'notes'              => "Grupo Telegram \"{$topic['title']}\" — señales de índice sintético. Participa en la expulsión semanal de miembros free.",
                ]
            );
        }

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_WHATSAPP, 'name' => 'AET-SAS'],
            [
                'purpose'       => Channel::PURPOSE_GENERAL,
                'is_active'     => true,
                'is_exclusive'  => true,
                'chat_id'       => (string) config('affiliates.whatsapp_group.group_jid', ''),
                'instance_name' => 'AET-SAS',
                'server_url'    => 'https://evoapi.abigailsoft.com',
                'api_key'       => (string) config('affiliates.whatsapp_group.apikey', ''),
                'notes'         => 'Importado automáticamente desde config/affiliates.php. Participa en la expulsión semanal de miembros free.',
            ]
        );
    }
}
