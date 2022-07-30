<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

use Myth\LaravelTools\Http\Resources\ApiCollectionResponse;
use Myth\LaravelTools\Http\Resources\ApiResource;

return [
    /*
    |--------------------------------------------------------------------------
    | Application Locales Configuration
    |--------------------------------------------------------------------------
    |
    | Define the languages used by the application.
    |
    */
    'locales'                       => (array) ['ar', 'en'],

    /*
    |--------------------------------------------------------------------------
    | Application support user
    |--------------------------------------------------------------------------
    |
    | Define the user attributes of the application.
    |
    */
    'support'                       => (array) [
        'username' => 'support',
        'name_ar'  => 'Support',
        'name_en'  => 'Support',
        'email'    => 'mythpe@gmail.com',
        'mobile'   => '0590470092',
    ],

    /** Application one user login */
    'one_login'                     => !1,

    /** Country Code */
    'country_code'                  => 'SAU',

    /** Currency Code */
    'currency_code'                 => 'SAR',

    /** Currency Symbol */
    'currency_symbol'               => 'SAR',

    /** Currency Balance */
    'currency_balance'              => 1,

    /** App date formats */
    "date_format"                   => [
        'long_date'        => "d M, Y",
        'date'             => "Y-m-d",
        'date-reverse'     => "d-m-Y",
        'datetime'         => "Y-m-d g:i a",
        'day'              => "l",
        'hijri_date'       => "Y/m/d",
        'time'             => "H:i",
        'time_string'      => "g:i a",
        'time_12'          => "g:i",
        'full'             => "Y-m-d H:i:s",
        'full_12'          => "Y-m-d g:i a",
        'human'            => "Y-m-d g:i a",
        'human_full'       => "l Y-m-d g:i a",
        'hijri_human'      => "Y/m/d g:i a",
        'hijri_human_full' => "l Y/m/d g:i a",
        'log'              => 'Y-m-d',
    ],

    /**
     * Enable sms messages
     */
    'send_sms'                      => (bool) env('SEND_SMS', !1),

    /**
     * Production url to website_end
     */
    'website_url'                   => (string) env('WEBSITE_URL', 'http://192.168.1.34'),

    /**
     * Production url to front end
     */
    'front_end_url'                 => (string) env('FRONT_END_URL', ''),

    /**
     * Production of Expo access token
     */
    'expo'                          => [
        'access_token' => (string) env('EXPO_ACCESS_TOKEN', ''),
    ],

    /**
     * API response class
     */
    'api_collection_response_class' => ApiCollectionResponse::class,

    /**
     * API resource class
     */
    'api_resources_class'           => ApiResource::class,

    /**
     * Media Resource class
     */
    'media_resource_class'          => 'App\Http\Resources\MediaResource',

    /**
     * Layout of export view using snappy pdf
     */
    'snappy_pdf_view'               => '4myth-tools::layouts.pdf_table',

    /**
     * Name of lang files
     */
    'js_lang_command_files'         => (array) ['attributes', 'choice'],

    /**
     * Permissions to skip
     */
    'skip_permission_ends_with'     => (array) ['.', '.allIndex', '.indexResource'],
];
