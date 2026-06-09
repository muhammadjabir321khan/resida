<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class UserSetting extends Model
{
    use BelongsToLandlord;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /** @var list<string> */
    public const ENCRYPTED_KEYS = [
        'stripe_secret_key',
        'stripe_webhook_secret',
        'smtp_password',
    ];

    public static function isEncryptedKey(string $key): bool
    {
        return in_array($key, self::ENCRYPTED_KEYS, true);
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        if (! auth()->check()) {
            return $default;
        }

        return self::getValueForUser((int) auth()->id(), $key, $default);
    }

    public static function getValueForUser(int $userId, string $key, ?string $default = null): ?string
    {
        $row = static::withoutLandlordScope()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();

        if (! $row || $row->value === null || $row->value === '') {
            return $default;
        }

        return self::decryptStored($key, (string) $row->value);
    }

    public static function put(string $key, ?string $value): void
    {
        if (! auth()->check()) {
            return;
        }

        if ($value === null || $value === '') {
            static::query()->where('key', $key)->delete();

            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => self::encryptForStorage($key, $value)]
        );
    }

    public static function hasNonEmptyValue(string $key): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return self::hasNonEmptyValueForUser((int) auth()->id(), $key);
    }

    public static function hasNonEmptyValueForUser(int $userId, string $key): bool
    {
        return static::withoutLandlordScope()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->exists();
    }

    private static function encryptForStorage(string $key, string $value): string
    {
        if (self::isEncryptedKey($key)) {
            return Crypt::encryptString($value);
        }

        return $value;
    }

    private static function decryptStored(string $key, string $stored): string
    {
        if (! self::isEncryptedKey($key)) {
            return $stored;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return '';
        }
    }
}
