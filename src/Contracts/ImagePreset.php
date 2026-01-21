<?php

namespace Noo\StatamicImageboss\Contracts;

interface ImagePreset
{
    /**
     * @return array{min: int, max: int, ratio?: float, interval?: int}
     */
    public function config(): array;
}
