<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\PaymentGetaway;

trait HasMetadataTrait
{
    public function metaData(): array
    {
        if ($meta = ($this->metaData ?? null)) {
            if (($decode = base64_decode($meta, !0)) !== false) {
                return json_decode($decode, true) ?? [];
            }
            return json_decode($meta, true) ?? [];
        }
        return [];
    }
}
