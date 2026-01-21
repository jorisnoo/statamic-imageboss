<?php

namespace Noo\StatamicImageboss;

use Statamic\Assets\Asset;
use Statamic\Fields\Value;

class ImageBoss
{
    public function from(mixed $asset): ImageBossBuilder
    {
        if ($asset instanceof Value) {
            $asset = $asset->value();
        }

        if (! $asset instanceof Asset) {
            throw new \InvalidArgumentException(
                'ImageBoss::from() expects an Asset instance, got '.get_debug_type($asset)
            );
        }

        return new ImageBossBuilder($asset);
    }
}
