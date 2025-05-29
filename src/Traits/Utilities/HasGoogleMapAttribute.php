<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

/**
 * @property double $latitude
 * @property double $longitude
 * @property-read string|null $google_map_url
 * @property-read string|null $google_map_iframe
 * @property-read string|null $google_map_search_api
 * @property-read string|null $google_map_place_api
 */
trait HasGoogleMapAttribute
{
    /**
     * $this->google_map_url
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
        return $this->getGoogleMapDirApi($this->latitude, $this->longitude);
    }

    /**
     * @param float|null $lat
     * @param float|null $lng
     *
     * @return string|null
     */
    public function getGoogleMapDirApi(float | null $lat = null, float | null $lng = null): ?string
    {
        $lat = $lat ?: $this->latitude;
        $lng = $lng ?: $this->longitude;
        return $lat && $lng ? "https://www.google.com/maps/dir/?api=1&destination=".urlencode("{$lat},{$lng}") : null;
    }

    /**
     * $this->google_map_iframe
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
        return ($lat && $lng) ? "https://maps.google.com/maps?q=".urlencode("{$lat},{$lng}")."&hl=".app()->getLocale()."&z=14&output=embed" : null;
    }

    /**
     * $this->google_map_search_api
     *
     * @return string|null
     */
    public function getGoogleMapSearchApiAttribute(): ?string
    {
        return $this->getGoogleMapDirApi();
    }

    /**
     * @param float|null $lat
     * @param float|null $lng
     *
     * @return string|null
     */
    public function getGoogleMapSearchApi(float | null $lat = null, float | null $lng = null): ?string
    {
        $lat = $lat ?: $this->latitude;
        $lng = $lng ?: $this->longitude;
        return $lat && $lng ? "https://www.google.com/maps/search/?api=1&query=".urlencode("{$lat},{$lng}") : null;
    }

    /**
     * $this->google_map_place_api
     *
     * @return string|null
     */
    public function getGoogleMapPlaceApiAttribute(): ?string
    {
        return $this->getGoogleMapPlaceApi();
    }

    /**
     * @param int $z
     * @param float|null $lat
     * @param float|null $lng
     *
     * @return string|null
     */
    public function getGoogleMapPlaceApi(int $z = 17, float | null $lat = null, float | null $lng = null): ?string
    {
        $lat = $lat ?: $this->latitude;
        $lng = $lng ?: $this->longitude;
        return $lat && $lng ? "https://www.google.com/maps/place/".urlencode("@{$lat},{$lng},{$z}") : null;
    }
}
