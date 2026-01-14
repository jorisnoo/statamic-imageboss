<?php

namespace Noo\StatamicImageboss\Facades;

use Illuminate\Support\Facades\Facade;
use Noo\StatamicImageboss\ImageBossBuilder;
use Statamic\Assets\Asset;

/**
 * @method static ImageBossBuilder from(Asset $asset)
 *
 * @see \Noo\StatamicImageboss\ImageBoss
 */
class ImageBoss extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Noo\StatamicImageboss\ImageBoss::class;
    }
}
