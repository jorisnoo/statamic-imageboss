<?php

namespace Noo\StatamicImageboss;

use Statamic\Assets\Asset;

final class OriginPath
{
    public static function for(Asset $asset, ?string $path = null): string
    {
        $container = $asset->container();
        $handle = $container->handle();
        $prefix = config('statamic.imageboss.path_prefixes', [])[$handle] ?? $container->diskHandle() ?? $handle;
        $segments = explode('/', str_replace('\\', '/', $prefix.'/'.($path ?? $asset->path())));
        $encoded = [];

        foreach ($segments as $segment) {
            if ($segment !== '' && $segment !== '.' && $segment !== '..') {
                $encoded[] = rawurlencode($segment);
            }
        }

        return implode('/', $encoded);
    }
}
