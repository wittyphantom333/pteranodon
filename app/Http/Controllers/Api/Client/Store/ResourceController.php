<?php

namespace Pteranodon\Http\Controllers\Api\Client\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Pteranodon\Exceptions\DisplayException;
use Pteranodon\Services\Store\ResourcePurchaseService;
use Pteranodon\Transformers\Api\Client\Store\CostTransformer;
use Pteranodon\Transformers\Api\Client\Store\UserTransformer;
use Pteranodon\Http\Controllers\Api\Client\ClientApiController;
use Pteranodon\Http\Requests\Api\Client\Store\PurchaseResourceRequest;

class ResourceController extends ClientApiController
{
    /**
     * ResourceController constructor.
     */
    public function __construct(private ResourcePurchaseService $purchaseService)
    {
        parent::__construct();
    }

    /**
     * Get the resources for the authenticated user.
     *
     * @throws DisplayException
     */
    public function user(Request $request)
    {
        return $this->fractal->item($request->user())
            ->transformWith($this->getTransformer(UserTransformer::class))
            ->toArray();
    }

    /**
     * Get the cost of resources.
     *
     * @throws DisplayException
     */
    public function costs(Request $request)
    {
        $data = [];
        $prefix = 'pteranodon::store:cost:';
        $types = ['cpu', 'memory', 'disk', 'slot', 'port', 'backup', 'database'];

        foreach ($types as $type) {
            array_push($data, $this->settings->get($prefix . $type, 0));
        }

        return $this->fractal->item($data)
            ->transformWith($this->getTransformer(CostTransformer::class))
            ->toArray();
    }

    /**
     * Allows a user to earn credits via passive earning.
     *
     * @throws DisplayException
     */
    public function earn(Request $request)
    {
        $amount = $this->settings->get('pteranodon::earn:amount', 0);

        if ($this->settings->get('pteranodon::earn:enabled') != 'true') {
            throw new DisplayException('Credit earning is currently disabled.');
        }

        try {
            $request->user()->update(['store_balance' => $request->user()->store_balance + $amount]);
        } catch (DisplayException $ex) {
            throw new DisplayException('Unable to passively earn coins.');
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Allows users to purchase resources via the store.
     *
     * @throws DisplayException
     */
    public function purchase(PurchaseResourceRequest $request): JsonResponse
    {
        $this->purchaseService->handle($request);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
