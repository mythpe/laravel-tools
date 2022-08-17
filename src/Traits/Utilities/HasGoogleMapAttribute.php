<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\Utilities;

/**
 * @property-read string|null $google_map_url
 * @property-read string|null $google_map_iframe
 */
trait HasGoogleMapAttribute
{
    /**
     * $this->google_map_url
     * $this->googleMapUrl
     *
     * @param $value
     *
     * @return string|null
     */
    public function getGoogleMapUrlAttribute($value): ?string
    {
        if ($value) {
            return (string) $value;
        }
        $lat = $this->latitude;
        $lng = $this->longitude;
        return $lat && $lng ? "https://www.google.com/maps/place/{$lat},{$lng}" : null;
    }

    /**
     * $this->google_map_iframe
     * $this->googleMapIframe
     *
     * @param $value
     *
     * @return string|null
     */
    public function getGoogleMapIframeAttribute($value): ?string
    {
        if ($value) {
            return (string) $value;
        }
        $lat = $this->latitude;
        $lng = $this->longitude;
        return $lat && $lng ? "https://maps.google.com/maps?q={$lat},{$lng}&hl=es&z=14&output=embed" : null;
    }
}
