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
