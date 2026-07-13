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
     * aet_premium serves both class-reminder audiences: it always gets the
     * exclusive reminders, and it also gets the "everyone" reminders (which
     * are additionally broadcast to the AET SAS 2k26 channel). Since a
     * Channel row maps to a single purpose, aet_premium's chat_id is
     * registered under two rows so both purposes reach it.
     */
    public function run(): void
    {
        $botToken = (string) config('affiliates.telegram.bot_token', '');
        $aetPremiumChatId = (string) config('affiliates.telegram.groups.aet_premium', '');

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_TELEGRAM, 'name' => 'aet_premium'],
            [
                'purpose'   => Channel::PURPOSE_CLASS_REMINDER_PREMIUM,
                'is_active' => true,
                'chat_id'   => $aetPremiumChatId,
                'bot_token' => $botToken,
                'notes'     => 'Grupo Telegram AET Premium (exclusivo). También recibe recordatorios "para todos" vía el canal aet_premium_recordatorios_todos, y participa en la expulsión semanal de miembros free.',
            ]
        );

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_TELEGRAM, 'name' => 'aet_premium_recordatorios_todos'],
            [
                'purpose'   => Channel::PURPOSE_CLASS_REMINDER_ALL,
                'is_active' => true,
                'chat_id'   => $aetPremiumChatId,
                'bot_token' => $botToken,
                'notes'     => 'Mismo grupo que aet_premium; registrado aparte para que también reciba los recordatorios "para todos".',
            ]
        );

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_TELEGRAM, 'name' => 'aet_sas_2k26'],
            [
                'purpose'   => Channel::PURPOSE_CLASS_REMINDER_ALL,
                'is_active' => true,
                'chat_id'   => '-1003511484723',
                'bot_token' => $botToken,
                'notes'     => 'Canal Telegram "AET SAS 2k26" (@aetsas) — recordatorios de clases para todos los miembros.',
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
                    'purpose'    => Channel::PURPOSE_GENERAL,
                    'is_active'  => true,
                    'chat_id'    => $chatId,
                    'bot_token'  => $botToken,
                    'notes'      => 'Importado automáticamente desde config/affiliates.php. Usado también para la expulsión semanal de miembros free.',
                ]
            );
        }

        Channel::query()->updateOrCreate(
            ['type' => Channel::TYPE_WHATSAPP, 'name' => 'AET-SAS'],
            [
                'purpose'       => Channel::PURPOSE_GENERAL,
                'is_active'     => true,
                'chat_id'       => (string) config('affiliates.whatsapp_group.group_jid', ''),
                'instance_name' => 'AET-SAS',
                'server_url'    => 'https://evoapi.abigailsoft.com',
                'api_key'       => (string) config('affiliates.whatsapp_group.apikey', ''),
                'notes'         => 'Importado automáticamente desde config/affiliates.php.',
            ]
        );
    }
}
