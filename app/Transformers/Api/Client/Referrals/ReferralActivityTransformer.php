<?php

namespace Pteranodon\Transformers\Api\Client\Referrals;

use Pteranodon\Models\User;
use Pteranodon\Models\ReferralUses;
use Pteranodon\Transformers\Api\Client\BaseClientTransformer;

class ReferralActivityTransformer extends BaseClientTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getResourceName(): string
    {
        return ReferralUses::RESOURCE_NAME;
    }

    /**
     * Transform this model into a representation that can be consumed by a client.
     *
     * @return array
     */
    public function transform(ReferralUses $model)
    {
        return [
            'code' => $model->code_used,
            'user_id' => $model->user_id,
            'created_at' => $model->created_at->toIso8601String(),
            'user_email' => User::where('id', $model->user_id)->first()->email,
        ];
    }
}
