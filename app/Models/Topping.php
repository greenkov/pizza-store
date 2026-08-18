<?php

namespace App\Models;

use Database\Factories\ToppingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topping extends Model
{
    /** @use HasFactory<ToppingFactory> */
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'md_cal',
        'md_price',
    ];

    /**
     * @var string[]
     */
    private static $toppingsCodeToNameMap = [
        'MSHR_1' => 'Champignons',
        'MSHR_2' => 'Marinated Porcini',
        'SSG_1' => 'Pepperoni',
        'SSG_2' => "Smoked Hunter's Sausage",
        'MT_1' => 'Ham',
        'MT_2' => 'Bacon',
        'MT_3' => 'Grilled Chicke',
        'CHZ_1' => 'Mozzarella',
        'CHZ_2' => 'Cheddar',
        'CHZ_3' => 'Parmesan',
        'BOARDS' => 'Cheese-Stuffed Crust',
    ];

    public static function getAvailableToppingCodes(): array
    {
        return array_keys(static::$toppingsCodeToNameMap);
    }

    /**
     * @throws \Exception
     */
    public static function getNameByCode(string $code): string
    {
        $result = static::$toppingsCodeToNameMap[$code] ?? null;

        if ($result === null) {
            throw new \Exception("Unknown Topping Code: $code");
        }

        return $result;
    }
}
