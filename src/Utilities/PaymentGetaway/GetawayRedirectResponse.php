<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities\PaymentGetaway;

use Myth\LaravelTools\Traits\PaymentGetaway\GetawayHelpersTrait;

abstract class GetawayRedirectResponse
{
    use GetawayHelpersTrait;

    /**
     * @var string|null
     */
    public ?string $PaymentId = null;
    /**
     * @var string|null
     */
    public ?string $TranId = null;
    /**
     * @var string|null
     */
    public ?string $ECI = null;
    /**
     * @var string|null
     */
    public ?string $Result = null;
    /**
     * @var string|null
     */
    public ?string $TrackId = null;
    /**
     * @var string|null
     */
    public ?string $AuthCode = null;
    /**
     * @var string|null
     */
    public ?string $ResponseCode = null;
    /**
     * @var string|null
     */
    public ?string $RRN = null;
    /**
     * @var string|null
     */
    public ?string $responseHash = null;
    /**
     * @var string|null
     */
    public ?string $cardBrand = null;
    /**
     * @var string|null
     */
    public ?string $amount = null;
    /**
     * @var string|null
     */
    public ?string $UserField1 = null;
    /**
     * @var string|null
     */
    public ?string $UserField3 = null;
    /**
     * @var string|null
     */
    public ?string $UserField4 = null;
    /**
     * @var string|null
     */
    public ?string $UserField5 = null;
    /**
     * @var string|null
     */
    public ?string $cardToken = null;
    /**
     * @var string|null
     */
    public ?string $maskedPAN = null;
    /**
     * @var string|null
     */
    public ?string $email = null;
    /**
     * @var string|null
     */
    public ?string $payFor = null;
    /**
     * @var string|null
     */
    public ?string $SubscriptionId = null;
    /**
     * @var string|null
     */
    public ?string $PaymentType = null;

    /** @var string|null data string base64 encoded */
    public ?string $metaData = null;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->fill($data);
    }

    /**
     * @return array
     */
    public function metaData(): array
    {
        if ($m = $this->metaData) {
            $r = json_decode(base64_decode($m), !0);
            return is_array($r) ? $r : [];
        }
        return [];
    }
}
