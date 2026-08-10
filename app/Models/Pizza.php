<?php

namespace App\Models;

use Database\Factories\PizzaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pizza extends Model
{
    /** @use HasFactory<PizzaFactory> */
    use HasFactory;

    public const TYPE_PRESET = 'preset';

    public const TYPE_CUSTOM = 'custom';
}
