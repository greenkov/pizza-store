<?php

namespace App\Models;

use Database\Factories\OrderedPizzaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $size
 * @property string $type
 * @property int|null $preset_id
 * @property array $topping_codes
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property numeric $price
 * @property string|null $name
 * @property int $order_id
 * @method static \Database\Factories\OrderedPizzaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza wherePresetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereToppingCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderedPizza whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
