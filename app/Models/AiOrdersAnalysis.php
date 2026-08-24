<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $report
 * @property array<array-key, mixed> $presets_recommendations
 * @property array<array-key, mixed> $new_hot_ids
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis whereNewHotIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis wherePresetsRecommendations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis whereReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiOrdersAnalysis whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AiOrdersAnalysis extends Model
{
    /**
     * @var string
     */
    protected $table = 'ai_orders_analysis';

    /**
     * @var string[]
     */
    protected $fillable = [
        'report',
        'presets_recommendations',
        'new_hot_ids',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'presets_recommendations' => 'array',
            'new_hot_ids' => 'array',
        ];
    }
}
