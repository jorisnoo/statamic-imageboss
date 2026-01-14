<?php

namespace Noo\StatamicImageboss\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Noo\StatamicImageboss\StatamicImageboss
 */
class StatamicImageboss extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Noo\StatamicImageboss\StatamicImageboss::class;
    }
}
