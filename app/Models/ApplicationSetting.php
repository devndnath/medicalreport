<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();
        if (! $row) {
            return $default;
        }

        return match ($row->type) {
            'int' => (int) $row->value,
            'bool' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($row->value, true),
            default => $row->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $type === 'json' ? json_encode($value) : $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
