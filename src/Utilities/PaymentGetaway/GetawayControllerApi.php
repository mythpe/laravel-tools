<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities\PaymentGetaway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Myth\LaravelTools\Models\Getaway\GetawayOrder;
use Myth\LaravelTools\Utilities\Logger;

abstract class GetawayControllerApi
{
    /**
     * @var GetawayRedirectResponse
     */
    public GetawayRedirectResponse $data;
    /**
     * @var array
     */
    public array $metaData = [];

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->data = new class($request->all()) extends GetawayRedirectResponse {
        };
        $this->metaData = $this->data->metaData();
        Logger::log($request->all(), 'urway/process-payment/'.date('Y-m-d'));
    }

    /**
     * @return bool
     */
    public function validateHash(): bool
    {
        return GetawayApi::validateResponseHash($this->data->TranId, $this->data->ResponseCode, $this->data->amount, $this->data->responseHash);
    }

    /**
     * @return Model|null
     */
    public function getTrackable(): ?Model
    {
        return config('4myth-getaway.order_class', GetawayOrder::class)::byTrackId($this->data->TrackId);
    }
}