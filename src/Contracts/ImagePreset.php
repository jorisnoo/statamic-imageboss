<?php

namespace Noo\StatamicImageboss\Contracts;

interface ImagePreset
{
    /**
     * @return array{min: int, max: int, ratio?: float, interval?: int, animation?: bool}
     */
    public function config(): array;
}
