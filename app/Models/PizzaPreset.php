<?php

namespace App\Models;

use Database\Factories\PizzaPresetFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PizzaPreset extends Model
{
    /** @use HasFactory<PizzaPresetFactory> */
    use HasFactory, SoftDeletes;

    public const SIZE_SMALL = 'sm';
    public const SIZE_MEDIUM = 'md';
    public const SIZE_LARGE = 'lg';

    /**
     * @var array|string[]
     */
    public static array $availableSizes = [
        self::SIZE_SMALL,
        self::SIZE_MEDIUM,
        self::SIZE_LARGE,
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'topping_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'topping_codes' => 'array',
        ];
    }

    protected function toppingCodes(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => json_decode($value ?? '[]', true),
            set: function (array $codes) {
                sort($codes);

                return json_encode($codes);
            },
        );
    }
}
