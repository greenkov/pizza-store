<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $status
 * @property numeric $total_price
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderedPizza> $orderedPizzas
 * @property-read int|null $ordered_pizzas_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    public const string STATUS_PENDING = 'pending';

    public const string STATUS_PAID = 'paid';

    public const string STATUS_DELIVERING = 'delivering';

    public const string STATUS_COMPLETED = 'completed';

    public const string STATUS_CANCELED = 'canceled';

    /**
     * @var array|string[]
     */
    public static array $availableStatuses = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_DELIVERING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELED,
    ];

    /**
     * @var string
     */
    protected $table = 'orders';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'status',
        'total_price',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderedPizzas(): HasMany
    {
        return $this->hasMany(OrderedPizza::class);
    }
}
