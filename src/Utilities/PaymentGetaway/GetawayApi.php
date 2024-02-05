<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities\PaymentGetaway;

use Closure;
use Exception;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Myth\LaravelTools\Utilities\Logger;

/**
 *
 */
class GetawayApi
{
    /**
     * @var GetawayApi|null
     */
    private static ?self $instance = null;
    /** @var array merchant data */
    private array $merchant = [
        'terminalId'   => null,
        'apiKey'       => null,
        'password'     => null,
        'country'      => null,
        'currencyCode' => null,
    ];
    /**
     * API Mode.
     * @var bool
     */
    private bool $production = !1;

    /** @var string Language  AR|EN */
    private string $language = 'AR';

    /**
     *
     */
    public function __construct()
    {
        $this->merchant['terminalId'] = config('4myth-getaway.terminal_id', '');
        $this->merchant['apiKey'] = config('4myth-getaway.api_key', '');
        $this->merchant['password'] = config('4myth-getaway.password', '');
        $this->merchant['country'] = config('4myth-getaway.country', 'SA');
        $this->merchant['currencyCode'] = config('4myth-getaway.currency_code', 'SAR');
        self::$instance = &$this;
    }

    /**
     * @return self
     */
    public static function instance(): self
    {
        return new self();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        if ($name == 'make') {
            return self::$instance->instance();
        }
        if (method_exists(self::$instance, $name)) {
            return self::$instance->$name(...$arguments);
        }
        return null;
    }

    public static function getawayController(Request $request): GetawayControllerApi
    {
        return new class($request) extends GetawayControllerApi {
        };
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($name == 'production') {
            return $this->production;
        }
        if ($name == 'language') {
            return strtoupper($this->language);
        }
        return $this->merchant[$name] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if ($name == 'production') {
            $this->production = (bool) $value;
        }
        if ($name == 'language') {
            $def = 'ar';
            $value = !$value ? config('4myth-getaway.language', $def) : $value;
            $this->language = strtoupper($value ?: $def);
        }
        if (array_key_exists($name, $this->merchant)) {
            $this->merchant[$name] = (string) ($value ?: '');
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        return null;
    }

    /**
     * @param int|string|Closure $trackId Reference generated at Merchant Side
     * @param string $amount
     * @param string $email
     * @param int $action
     * @param string|null $transId Trans ID is required for all the transaction type except Purchase & Authorization.
     * Trans ID should be the Pay ID generated in the first leg of Purchase & Authorization.
     * @param array|null $metaData
     * @param array|null $customer
     * @return GetawayTransactionResult
     */
    private function transaction(int | string | Closure $trackId, string $amount, string $email, int $action, ?string $transId = null, ?array $metaData = null, ?array $customer = null): GetawayTransactionResult
    {
        //Generate Track ID
        $trackId = (string) (is_callable($trackId) ? $trackId($this) : $trackId);
        $callbackUrl = route(config('4myth-getaway.callback_route_name'));
        $postResponse = null;
        $result = [
            'message'            => null,
            'exception'          => null,
            'payment_target_url' => null,
        ];

        if (!$transId && !in_array($action, [config('4myth-getaway.actions.purchase'), config('4myth-getaway.actions.authorization')])) {
            throw new InvalidArgumentException('transid is required for all the transaction type except Purchase and Authorization.');
        }
        try {
            $customer = $customer ?: [];
            // $metaData = array_merge($metaData ?: [], ['action' => $action]);
            $metaData = array_merge($metaData ?: [], []);
            $fields = [
                'trackid'       => $trackId,
                'terminalId'    => $this->terminalId,
                'customerEmail' => $email,
                'First_name'    => $customer['first_name'] ?? null,
                'Last_name'     => $customer['last_name'] ?? null,
                'Address'       => $customer['address'] ?? null,
                'City'          => $customer['city'] ?? null,
                'State'         => $customer['state'] ?? null,
                'Zip'           => $customer['zip'] ?? null,
                'Phoneno'       => $customer['mobile'] ?? null,
                'action'        => "$action",
                'merchantIp'    => $this->serverIp(),
                'password'      => $this->password,
                'currency'      => $this->currencyCode,
                'country'       => $this->country,
                'amount'        => "$amount",
                'requestHash'   => $this->generateTransactionHash($trackId, $amount),  //generated Hash
                'udf1'          => '',
                'udf2'          => $callbackUrl, // Callback URL
                'udf3'          => $this->language, //Payment Page Language,
                'metaData'      => json_encode($metaData, JSON_UNESCAPED_UNICODE),
            ];
            // dd($fields);
            if ($transId) {
                $fields['transid'] = $transId;
            }
            $postResponse = $this->post($fields);
            $message = trans_has($k = '4myth-getaway.codes.'.($postResponse['responseCode'] ?? null)) ? __($k) : config($k);
            $result['message'] = $message;
        }
        catch (Exception $exception) {
            $result['exception'] = $exception;
            $result['message'] = $exception->getMessage();
        }

        if (($payId = $postResponse['payid'] ?? null) && ($targetUrl = $postResponse['targetUrl'] ?? null)) {
            $result['payment_target_url'] = "{$targetUrl}?paymentid={$payId}";
        }
        if (config('4myth-getaway.enable_log', !1)) {
            Logger::log($result, $this->getLogName());
        }
        return new class(array_merge($result, (array) $postResponse)) extends GetawayTransactionResult {
        };
    }

    /**
     * @param string|null $prefix
     * @return string
     */
    private function getBaseUrl(?string $prefix = null): string
    {
        $base = trim(config('4myth-getaway.base_url', [])[$this->production ? 'prod' : 'dev'] ?? '', '\/');
        return "$base".($prefix ? "/".trim($prefix, '\/') : '');
    }

    /**
     * @return string
     */
    private function serverIp(): string
    {
        return request()->server('SERVER_ADDR') ?: '';
    }

    /**
     * @return string
     */
    private function getTransactionUrl(): string
    {
        return trim($this->getBaseUrl(config('4myth-getaway.urls.transaction')), '\/');
    }

    /**
     * @param array $fields
     * @return mixed
     */
    private function post(array $fields): mixed
    {
        $url = $this->getTransactionUrl();
        $data = json_encode($fields, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($data),
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // Execute post
        $exec = curl_exec($ch);
        // Close connection
        curl_close($ch);
        return $result = json_decode($exec, !0);
    }

    /**
     * @return string
     */
    private function getLogName(): string
    {
        return config('4myth-getaway.folder_log_name')."/".now()->format('Ymd').'.log';
    }

    /**
     * Generate a new Hash of transaction
     *
     * @param string $trackId
     * @param string $amount
     *
     * @return string
     */
    private function generateTransactionHash(string $trackId, string $amount): string
    {
        return hash('sha256', "$trackId|{$this->terminalId}|$this->password|$this->apiKey|$amount|$this->currencyCode");
    }

    /**
     * Generate a new Hash of inquiry
     *
     * @param string $tranId trans id from UrWay
     * @param string $responseCode the code from response of transaction from UrWay
     * @param string $amount the amount of transaction
     *
     * @return string
     */
    private function generateResponseHash(string $tranId, string $responseCode, string $amount): string
    {
        $responseHash = "$tranId|$this->apiKey|$responseCode|$amount";
        return hash('sha256', $responseHash);
    }

    /**
     * Check from the response of make transaction is valid and secure
     *
     * @param string $transId
     * @param string $responseCode
     * @param string $amount
     * @param string $responseHash
     *
     * @return bool
     */
    private function validateResponseHash(string $transId, string $responseCode, string $amount, string $responseHash): bool
    {
        return $this->generateResponseHash($transId, $responseCode, $amount) === $responseHash;
    }
}