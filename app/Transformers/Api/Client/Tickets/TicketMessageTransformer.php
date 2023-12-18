<?php

namespace Pteranodon\Transformers\Api\Client\Tickets;

use Pteranodon\Models\User;
use Pteranodon\Models\TicketMessage;
use Pteranodon\Transformers\Api\Client\BaseClientTransformer;

class TicketMessageTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return TicketMessage::RESOURCE_NAME;
    }

    /**
     * Transform a ticket model into a representation that can be returned
     * to a client.
     */
    public function transform(TicketMessage $model): array
    {
        return [
            'id' => $model->id,
            'user_email' => User::where('id', $model->user_id)->first()->email ?? 'system',
            'content' => $model->content,
            'created_at' => $model->created_at->toAtomString(),
            'updated_at' => $model->updated_at->toAtomString(),
        ];
    }
}
