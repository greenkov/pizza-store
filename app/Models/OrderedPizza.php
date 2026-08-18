<?php

namespace App\Models;

use Database\Factories\OrderedPizzaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderedPizza extends Model
{
    /** @use HasFactory<OrderedPizzaFactory> */
    use HasFactory;

    public const TYPE_PRESET = 'preset';

    public const TYPE_CUSTOM = 'custom';

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
     * @var string
     */
    protected $table = 'pizzas';

    /**
     * @var string[]
     */
    protected $fillable = [
        'size',
        'type',
        'preset_id',
        'order_id',
        'price',
        'topping_codes',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'topping_codes' => 'array',
        ];
    }

    /**
     * @return Attribute
     */
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
