<?php

namespace Noo\StatamicImageboss;

enum Preset: string
{
    case Default = 'default';
    case Thumbnail = 'thumbnail';
    case Card = 'card';
    case Hero = 'hero';

    /**
     * @return array{min: int, max: int, ratio?: float, interval?: int}
     */
    public function config(): array
    {
        return match ($this) {
            self::Default => ['min' => 320, 'max' => 2560],
            self::Thumbnail => ['min' => 200, 'max' => 700, 'ratio' => 1, 'interval' => 250],
            self::Card => ['min' => 300, 'max' => 800, 'ratio' => 4 / 5],
            self::Hero => ['min' => 640, 'max' => 3840],
        };
    }
}
