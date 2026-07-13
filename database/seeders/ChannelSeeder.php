<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Seeds the channels already in use by the app/n8n so nothing breaks
     * after switching to DB-driven channel administration. The two
     * class-reminder purposes (all members / premium exclusive) are left
     * for the admin to assign via the Canales screen since only the
     * business knows which physical group should serve each purpose.
     */
    public function run(): void
    {
        $botToken = (string) config('affiliates.telegram.bot_token', '');

        $telegramGroups = [
            'aet_premium'      => (string) config('affiliates.telegram.groups.aet_premium', ''),
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
                    'notes'      => 'Importado automáticamente desde config/affiliates.php.',
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
