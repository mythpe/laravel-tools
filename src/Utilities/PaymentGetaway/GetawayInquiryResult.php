<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities\PaymentGetaway;

use Exception;
use Myth\LaravelTools\Traits\PaymentGetaway\GetawayHelpersTrait;
use Myth\LaravelTools\Traits\PaymentGetaway\HasMetadataTrait;

abstract class GetawayInquiryResult
{
    use GetawayHelpersTrait;
    use HasMetadataTrait;

    /**
     * Request data
     * @var array
     */
    public array $request = [];

    /**
     * @var Exception|null
     */
    public ?Exception $exception = null;

    /** @var bool Response is 000 */
    public bool $success = !1;

    /** @var string|null Message string from response */
    public ?string $message = null;

    /** @var string|null Message string from response */
    public ?string $result = null;
    /**
     * @var string|null
     */
    public ?string $responseCode = null;
    /**
     * @var string|null
     */
    public ?string $authcode = null;
    /**
     * @var string|null
     */
    public ?string $tranid = null;
    /**
     * @var string|null
     */
    public ?string $trackid = null;
    /**
     * @var string|null
     */
    public ?string $udf1 = null;
    /**
     * @var string|null
     */
    public ?string $udf2 = null;
    /**
     * @var string|null
     */
    public ?string $udf3 = null;
    /**
     * @var string|null
     */
    public ?string $udf4 = null;
    /**
     * @var string|null
     */
    public ?string $udf = null;
    /**
     * @var string|null
     */
    public ?string $rrn = null;
    /**
     * @var string|null
     */
    public ?string $eci = null;
    /**
     * @var string|null
     */
    public ?string $subscriptionId = null;
    /**
     * @var string|null
     */
    public ?string $trandate = null;
    /**
     * @var string|null
     */
    public ?string $tranType = null;
    /**
     * @var string|null
     */
    public ?string $integrationModule = null;
    /**
     * @var string|null
     */
    public ?string $integrationData = null;
    /**
     * @var string|null
     */
    public ?string $payid = null;
    /**
     * @var string|null
     */
    public ?string $targetUrl = null;
    /**
     * @var string|null
     */
    public ?string $postData = null;
    /**
     * @var string|null
     */
    public ?string $intUrl = null;
    /**
     * @var string|null
     */
    public ?string $responseHash = null;
    /**
     * @var string|null
     */
    public ?string $amount = null;
    /**
     * @var string|null
     */
    public ?string $cardBrand = null;
    /**
     * @var string|null
     */
    public ?string $maskedPAN = null;
    /**
     * @var string|null
     */
    public ?string $linkBasedUrl = null;
    /**
     * @var string|null
     */
    public ?string $sadadNumber = null;
    /**
     * @var string|null
     */
    public ?string $billNumber = null;
    /**
     * @var string|null
     */
    public ?string $paymentType = null;
    /**
     * @var string|null
     */
    public ?string $cardToken = null;
    /**
     * @var string|null
     */
    public ?string $metaData = null;

    /** @var GetawayApi|null */
    public ?GetawayApi $api = null;

    /**
     * @param array $data
     * @param GetawayApi|null $api
     */
    public function __construct(array $data = [], ?GetawayApi $api = null)
    {
        $this->fill($data);
        $this->message = trans_has($k = '4myth-getaway.codes.'.($data['responseCode'] ?? '')) ? __($k) : config($k, ($data['result'] ?? ($data['message'] ?? null)));
        $this->success = $this->responseCode == '000';
        $this->request = $data;
        $this->api = $api;
    }

}
