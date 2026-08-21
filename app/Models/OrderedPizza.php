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

    public const string TYPE_PRESET = 'preset';

    public const string TYPE_CUSTOM = 'custom';

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
        'name',
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
