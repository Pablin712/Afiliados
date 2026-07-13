<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    public const TYPE_TELEGRAM = 'telegram';
    public const TYPE_WHATSAPP = 'whatsapp';

    // Reminder de clase para todos los miembros.
    public const PURPOSE_CLASS_REMINDER_ALL = 'class_reminder_all';
    // Reminder de clase exclusivo para miembros premium.
    public const PURPOSE_CLASS_REMINDER_PREMIUM = 'class_reminder_premium';
    // Grupos heredados / sin función de recordatorios asignada todavía.
    public const PURPOSE_GENERAL = 'general';

    protected $fillable = [
        'type',
        'name',
        'purpose',
        'is_active',
        'is_exclusive',
        'chat_id',
        'bot_token',
        'instance_name',
        'server_url',
        'api_key',
        'notes',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_exclusive' => 'boolean',
        'bot_token'    => 'encrypted',
        'api_key'      => 'encrypted',
    ];

    /**
     * @return array<string, string>
     */
    public static function purposes(): array
    {
        return [
            self::PURPOSE_CLASS_REMINDER_ALL     => 'Recordatorio de clases · Todos los miembros',
            self::PURPOSE_CLASS_REMINDER_PREMIUM => 'Recordatorio de clases · Premium (exclusivo)',
            self::PURPOSE_GENERAL                => 'General / sin función asignada',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_TELEGRAM => 'Telegram',
            self::TYPE_WHATSAPP => 'WhatsApp',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePurpose(Builder $query, string $purpose): Builder
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeExclusive(Builder $query): Builder
    {
        return $query->where('is_exclusive', true);
    }
}
