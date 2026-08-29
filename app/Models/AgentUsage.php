<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $agent_name
 * @property string $conversation_uuid
 * @property int $input_tokens
 * @property int $output_tokens
 * @property int $total_tokens
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereAgentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereConversationUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereInputTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereOutputTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereTotalTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentUsage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AgentUsage extends Model
{
    /**
     * @var string
     */
    protected $table = 'agent_usages';

    /**
     * @var string[]
     */
    protected $fillable = [
        'agent_name',
        'conversation_uuid',
        'input_tokens',
        'output_tokens',
        'total_tokens',
    ];
}
