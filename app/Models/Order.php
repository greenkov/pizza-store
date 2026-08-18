<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DELIVERING = 'delivering';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELED = 'canceled';

    /**
     * @var array|string[]
     */
    public static array $availableStatuses = [
        self::STATUS_PENDING,
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
}
