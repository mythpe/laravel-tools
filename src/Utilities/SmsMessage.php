<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 *
 */
class SmsMessage
{
    /**
     * API username
     *
     * @var string|null
     */
    public string | null $username = null;
    /**
     * API sender Name
     *
     * @var string|null
     */
    public string | null $sender = null;
    /**
     * API password
     *
     * @var string|null
     */
    public string | null $password = null;
    /**
     * Type of data will be returned from getaway
     *
     * @var string
     */
    public string $returnType = 'json';
    /**
     * unicode type of content.
     * @var string
     */
    public string $unicode = 'u';
    /**
     * @var array Http query params
     */
    public array $data = [];
    /** @var bool Debug request */
    public bool $debug = !1;
    /** @var bool Log send */
    public bool $logger = !0;
    /** @var array<string, string> */
    public array $segments = [
        'send_sms'     => 'sendsms',
        'balance'      => 'balance',
        'sender_names' => 'sender_names',
    ];
    /**
     * @var string
     */
    public string $usernameKey = 'username';
    /**
     * @var string
     */
    public string $messageKey = 'message';
    /**
     * @var string
     */
    public string $senderKey = 'sender';
    /**
     * @var string
     */
    public string $passwordKey = 'api_key';
    /** @var string */
    public string $returnTypeKey = 'return';
    /** @var string */
    // public string $unicodeKey = 'return';
    public string $unicodeKey = 'unicode';
    /**
     * @var string
     */
    public string $numbersKey = 'numbers';
    /** @var string Log Folder Name */
    public string $logName = 'sms';
    /** @var PendingRequest $http */
    public PendingRequest $http;
    /**
     * @var bool Send as form post.
     */
    public bool $asForm = !0;
    /**
     * API domain url
     *
     * @var string|null
     */
    protected string | null $baseUrl = null;
    /** @var string */
    protected string $method = 'POST';

    /**
     *
     */
    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('4myth-tools.sms.url', ''), '/');
        $this->username = (string) config('4myth-tools.sms.username', '');
        $this->password = (string) config('4myth-tools.sms.password', '');
        $this->sender = (string) env('4myth-tools.sms.sender', '');
        $this->http = Http::baseUrl($this->getBaseUrl())->withHeader('X-REQUEST-WITH', "MyTh SMS API 2.0");
    }

    /**
     * @param $message
     * @param $numbers
     *
     * @return false|string|null|mixed
     */
    public static function sendSms($message, $numbers): mixed
    {
        return (new self())->send($message, $numbers);
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function logName(?string $name = null): string
    {
        $name = $name ?? now()->format(config('4myth-tools.date_format.log'));
        $name = Str::finish($name, '.log');
        return $logName = "$this->logName/$name";
    }

    /**
     * @param $data
     * @param string|null $fileName
     * @return void
     */
    public function log($data, ?string $fileName = null): void
    {
        if (!$this->logger) {
            return;
        }
        Logger::log($data, $this->logName($fileName));
    }

    /**
     * @param string|null $segments
     * @return string
     */
    public function getBaseUrl(?string $segments = null): string
    {
        return $this->baseUrl.($segments ? "/{$segments}" : '');
    }

    /**
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * @param $message
     * @param $numbers
     * @return array|false|mixed|null
     */
    public function send($message, $numbers): mixed
    {
        if (!$message || !$numbers) {
            $this->log([
                'error'     => !0,
                'message'   => $message,
                'numbers'   => $numbers,
                'exception' => $this,
            ]);
            return null;
        }
        try {
            $this->usernameKey = config('4myth-tools.sms.keys.username', $this->usernameKey);
            $this->passwordKey = config('4myth-tools.sms.keys.password', $this->passwordKey);
            $this->senderKey = config('4myth-tools.sms.keys.sender', $this->senderKey);
            $this->numbersKey = config('4myth-tools.sms.keys.numbers', $this->numbersKey);
            $this->messageKey = config('4myth-tools.sms.keys.message', $this->messageKey);
            $this->returnTypeKey = config('4myth-tools.sms.keys.return_type', $this->returnTypeKey);
            $this->unicodeKey = config('4myth-tools.sms.keys.unicode', $this->unicodeKey);

            /** @var Response $request */
            $arg = [
                $this->segments['send_sms'],
                array_filter([
                    $this->usernameKey   => $this->username,
                    $this->passwordKey   => $this->password,
                    $this->senderKey     => $this->sender,
                    $this->numbersKey    => is_array($numbers) ? implode(',', $numbers) : $numbers,
                    $this->messageKey    => trim($message),
                    $this->returnTypeKey => $this->returnType,
                    $this->unicodeKey    => $this->unicode,
                    ...$this->data,
                ]),
            ];
            if ($this->debug) {
                return $this->http->dd()->{$this->method}(...$arg);
            }
            if ($this->asForm) {
                $request = $this->http->asForm()->{$this->method}(...$arg);
            }
            else {
                $request = $this->http->{$this->method}(...$arg);
            }
            $res = $request->json();
            $this->log($res);
            return $request;
        }
        catch (Exception$e) {
            Logger::log($e);
            return false;
        }
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method): SmsMessage
    {
        $this->method = strtolower($method);
        return $this;
    }

    /**
     * Example:
     * [
     * 'status' => [
     *      'code' => 200 ,
     *      'message' => 'success' ,
     *      'error' => false,
     *      'validation_errors' => []
     * ],
     *  'data' => [ 'balance' => 1111 ]
     * ]
     * @return array|false|mixed
     */
    public function getBalance(): mixed
    {
        try {
            $request = $this->http->get($this->segments['balance'], [
                $this->usernameKey => $this->username,
                $this->passwordKey => $this->password,
                ...$this->data,
            ]);
            $res = $request->json();
            $this->log($res);
            return $res;
        }
        catch (Exception$e) {
            $this->log($e);
            return false;
        }
    }

    /**
     * Example:
     * [
     *  'status' => [
     *      'code' => 200 ,
     *      'message' => 'success' ,
     *      'error' => false,
     *      'validation_errors' => []
     * ],
     *  'data' => [ 'sender_names' => string[] ]
     * ]
     * @return array|false|mixed
     */
    public function getSenderNames(): mixed
    {
        try {
            $request = $this->http->get($this->segments['sender_names'], [
                $this->usernameKey => $this->username,
                $this->passwordKey => $this->password,
                ...$this->data,
            ]);
            $res = $request->json();
            $this->log($res);
            return $res;
        }
        catch (Exception$e) {
            $this->log($e);
            return false;
        }
    }
}
