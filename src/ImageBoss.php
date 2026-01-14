<?php

namespace Noo\StatamicImageboss;

use Statamic\Assets\Asset;

class ImageBoss
{
    public function from(Asset $asset): ImageBossBuilder
    {
        return new ImageBossBuilder($asset);
    }
}
