<?php

namespace Pteranodon\Http\Controllers\Api\Client\Store;

use Stripe\StripeClient;
use Illuminate\Http\JsonResponse;
use Stripe\Exception\ApiErrorException;
use Pteranodon\Exceptions\DisplayException;
use Pteranodon\Http\Controllers\Api\Client\ClientApiController;
use Pteranodon\Http\Requests\Api\Client\Store\Gateways\StripeRequest;

class StripeController extends ClientApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws DisplayException|ApiErrorException
     */
    public function purchase(StripeRequest $request): JsonResponse
    {
        if (!$this->settings->get('pteranodon::store:stripe:enabled')) {
            throw new DisplayException('Unable to purchase via Stripe: module not enabled');
        }

        $client = new StripeClient(config('gateways.stripe.secret'));
        $amount = $request->input('amount');
        $cost = number_format(config('gateways.stripe.cost', 1.00) / 100 * $amount, 2);
        $currency = config('gateways.currency', 'USD');

        $checkout = $client->checkout->sessions->create([
            'success_url' => config('app.url') . '/store/credits',
            'cancel_url' => config('app.url'),
            'mode' => 'payment',
            'customer_email' => $request->user()->email,
            'metadata' => ['credit_amount' => $amount, 'user_id' => $request->user()->id],
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => str_replace('.', '', $cost),
                        'product_data' => [
                            'name' => $amount . ' Credits | ' . $this->settings->get('settings::app:name'),
                        ],
                    ],
                ],
            ],
        ]);

        return new JsonResponse($checkout->url, 200, [], null, true);
    }
}
