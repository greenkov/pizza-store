<?php

namespace App\Models;

use Database\Factories\PizzaPresetFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property array $topping_codes
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property string|null $image_path
 * @method static \Database\Factories\PizzaPresetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereToppingCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PizzaPreset withoutTrashed()
 * @mixin \Eloquent
 */
class PizzaPreset extends Model
{
    /** @use HasFactory<PizzaPresetFactory> */
    use HasFactory, SoftDeletes;

    public const string SIZE_SMALL = 'sm';

    public const string SIZE_MEDIUM = 'md';

    public const string SIZE_LARGE = 'lg';

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
