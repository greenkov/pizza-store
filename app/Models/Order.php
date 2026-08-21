<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
