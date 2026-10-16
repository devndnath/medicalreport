<?php

namespace App\Support;

/**
 * Holds which factory the current request is operating against. Set once
 * per request by App\Http\Middleware\SetCurrentFactory, then read by
 * App\Models\Concerns\BelongsToFactory's global scope so every
 * factory-scoped model query is automatically limited to it — no
 * controller needs to remember to add ->where('factory_id', ...) itself.
 *
 * For a normal user this is always their own factory_id. For a Super
 * Admin it is whichever single factory they are currently viewing
 * (stored in session), never more than one at a time — the two
 * factories' data is always shown separately, never merged.
 */
class CurrentFactory
{
    protected static ?int $id = null;

    public static function set(?int $id): void
    {
        static::$id = $id;
    }

    public static function id(): ?int
    {
        return static::$id;
    }

    public static function clear(): void
    {
        static::$id = null;
    }
}
