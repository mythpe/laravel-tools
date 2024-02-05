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

abstract class GetawayInquiryResult
{
    use GetawayHelpersTrait;

    public ?Exception $exception = null;

    /** @var bool Response is 000 */
    public bool $success = !1;

    /** @var string|null Message string from response */
    public ?string $message = null;

    /** @var string|null Message string from response */
    public ?string $result = null;
    public ?string $responseCode = null;
    public ?string $authcode = null;
    public ?string $tranid = null;
    public ?string $trackid = null;
    public ?string $udf1 = null;
    public ?string $udf2 = null;
    public ?string $udf3 = null;
    public ?string $udf4 = null;
    public ?string $udf = null;
    public ?string $rrn = null;
    public ?string $eci = null;
    public ?string $subscriptionId = null;
    public ?string $trandate = null;
    public ?string $tranType = null;
    public ?string $integrationModule = null;
    public ?string $integrationData = null;
    public ?string $payid = null;
    public ?string $targetUrl = null;
    public ?string $postData = null;
    public ?string $intUrl = null;
    public ?string $responseHash = null;
    public ?string $amount = null;
    public ?string $cardBrand = null;
    public ?string $maskedPAN = null;
    public ?string $linkBasedUrl = null;
    public ?string $sadadNumber = null;
    public ?string $billNumber = null;
    public ?string $paymentType = null;
    public ?string $cardToken = null;
    public ?string $metaData = null;

    public function __construct(array $request = [])
    {
        $this->fill($request);
        $this->message = trans_has($k = '4myth-getaway.codes.'.($request['responseCode'] ?? '')) ? __($k) : config($k, ($request['result'] ?? ($request['message'] ?? null)));
        $this->success = $this->responseCode == '000';
    }

}
