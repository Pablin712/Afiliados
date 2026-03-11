<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'affiliate_code',
        'identification',
        'password',
        'sponsor_id',
        'commission_balance',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function resolveAffiliateCode(?string $affiliateCode): ?self
    {
        if (! is_string($affiliateCode) || $affiliateCode === '') {
            return null;
        }

        if (! Schema::hasColumn('users', 'affiliate_code')) {
            return self::resolveAffiliateCodeFallback($affiliateCode);
        }

        return self::query()
            ->select(['id', 'name', 'affiliate_code'])
            ->whereRaw('LOWER(affiliate_code) = ?', [Str::lower($affiliateCode)])
            ->first();
    }

    public function currentAffiliateCode(): string
    {
        if (is_string($this->affiliate_code ?? null) && $this->affiliate_code !== '') {
            return $this->affiliate_code;
        }

        return self::buildAffiliateCode($this->name, (int) $this->id);
    }

    public static function buildAffiliateCode(string $name, int $id): string
    {
        $prefix = self::normalizeAffiliatePrefix($name);

        return $prefix.str_pad((string) $id, 3, '0', STR_PAD_LEFT);
    }

    protected static function normalizeAffiliatePrefix(string $name): string
    {
        $baseName = (string) Str::of($name)->trim()->before(' ');

        $prefix = (string) Str::of($baseName)
            ->ascii()
            ->replace(' ', '')
            ->replaceMatches('/[^A-Za-z0-9]/', '');

        return $prefix !== '' ? $prefix : 'USER';
    }

    protected static function resolveAffiliateCodeFallback(string $affiliateCode): ?self
    {
        if (! preg_match('/^(?<prefix>[A-Za-z0-9]+)(?<id>\d+)$/', $affiliateCode, $matches)) {
            return null;
        }

        $userId = (int) $matches['id'];
        if ($userId <= 0) {
            return null;
        }

        $user = self::query()->select(['id', 'name'])->find($userId);
        if ($user === null) {
            return null;
        }

        return strcasecmp(self::buildAffiliateCode($user->name, $user->id), $affiliateCode) === 0
            ? $user
            : null;
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    public function userBanks(): HasMany
    {
        return $this->hasMany(UserBank::class);
    }
}
