<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ImageBoss Source
    |--------------------------------------------------------------------------
    |
    | Your ImageBoss source identifier. When this is not set, the package
    | will automatically fall back to using Statamic's built-in Glide
    | for image transformations.
    |
    */

    'source' => env('IMAGEBOSS_SOURCE'),

    /*
    |--------------------------------------------------------------------------
    | ImageBoss Secret
    |--------------------------------------------------------------------------
    |
    | Optional HMAC secret for signing ImageBoss URLs. When set, all URLs
    | will be signed using SHA-256 to prevent URL tampering.
    |
    */

    'secret' => env('IMAGEBOSS_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | ImageBoss Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for ImageBoss CDN. You typically don't need to change this.
    |
    */

    'base_url' => 'https://img.imageboss.me',

    /*
    |--------------------------------------------------------------------------
    | Default Width
    |--------------------------------------------------------------------------
    |
    | The default width to use when url() is called without an explicit width.
    |
    */

    'default_width' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Width Interval
    |--------------------------------------------------------------------------
    |
    | The default step size when generating srcset width variants.
    |
    */

    'width_interval' => 200,

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    |
    | Define named presets for common image configurations. Each preset can
    | specify min/max widths, aspect ratio, and interval.
    |
    | Available options per preset:
    | - min: Minimum width for srcset (required)
    | - max: Maximum width for srcset (required)
    | - ratio: Aspect ratio as width/height (optional)
    | - interval: Width step size, overrides default (optional)
    |
    */

    'presets' => [
        'default' => [
            'min' => 320,
            'max' => 2560,
        ],
        'thumbnail' => [
            'min' => 200,
            'max' => 700,
            'ratio' => 1,
            'interval' => 250,
        ],
        'card' => [
            'min' => 300,
            'max' => 800,
            'ratio' => 4 / 5,
        ],
        'hero' => [
            'min' => 640,
            'max' => 3840,
        ],
    ],
];
