<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\Utilities;

use App\Notifications\PublicNotification;
use Exception;
use Illuminate\Support\Carbon;

trait TowFactorAuthTrait
{
    /**
     * @param  bool  $notify
     * [TOKEN, CODE, USER_ID]
     *
     * @return array
     */
    public function make2FactorCode(bool $notify = !1): array
    {
        [$token, $code] = $this->generate2FactorToken();
        try {
            $notify && $this->notify2FactorCode($code);
        }
        catch (Exception $exception) {
        }
        return [$token, $code];
    }

    /**
     * Generate 2-factor-auth code
     * return array [TOKEN,CODE]
     *
     * @return array
     */
    public function generate2FactorToken(): array
    {
        $time = now()->addMinutes($this->get2FactorMinutes())->timestamp;
        $code = rand(1000, 9999);
        $token = md5("2-factor-auth").base64_encode(json_encode([$time, $code, $this->id])).md5("token");
        return [$token, $code];
    }

    /**
     * Send 2-factor-auth code to user
     *
     * @param $code
     */
    public function notify2FactorCode($code): void
    {
        try {
            $notification = new PublicNotification(__("messages.login.2_factor_code", ['code' => $code]), __("mail.2_factor_auth"));
            $this->notify($notification);
        }
        catch (Exception $exception) {
        }
    }

    /**
     * Get minutes of 2-factor-auth
     *
     * @return int
     */
    public function get2FactorMinutes(): int
    {
        return 60;
    }

    /**
     * Validate the 2-factor-auth token
     * [Carbon,code,userId]
     *
     * @param $token
     *
     * @return array|null
     */
    public function validate2FactorToken($token): ?array
    {
        try {
            [$timestamp, $code, $userId] = json_decode(base64_decode(str_ireplace([md5("2-factor-auth"), md5("token")], '', $token), !0), !0);
            $time = Carbon::createFromTimestamp($timestamp);
            if (!$time->isPast()) {
                return [$time, $code, $userId];
            }
        }
        catch (Exception $exception) {
            developmentMode() && dd($exception);
        }
        return null;
    }
}
