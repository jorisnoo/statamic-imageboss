<?php

namespace Noo\StatamicImageboss\Facades;

use Illuminate\Support\Facades\Facade;
use Noo\StatamicImageboss\ImageBossBuilder;
use Statamic\Assets\Asset;
use Statamic\Fields\Value;

/**
 * @method static ImageBossBuilder from(Asset|Value $asset)
 *
 * @see \Noo\StatamicImageboss\ImageBoss
 * @see ImageBossBuilder::url() For fixed-dimension URLs
 * @see ImageBossBuilder::rias() For responsive URLs with {width} placeholder
 */
class ImageBoss extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Noo\StatamicImageboss\ImageBoss::class;
    }
}
