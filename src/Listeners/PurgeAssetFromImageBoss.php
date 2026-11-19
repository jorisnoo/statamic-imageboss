<?php

namespace Noo\StatamicImageboss\Listeners;

use Noo\StatamicImageboss\Jobs\PurgeImageBossCacheJob;
use Noo\StatamicImageboss\OriginPath;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

class PurgeAssetFromImageBoss
{
    public function handle(AssetUploaded|AssetReuploaded|AssetDeleted|AssetSaved $event): void
    {
        $source = config('statamic.imageboss.source');
        $baseUrl = config('statamic.imageboss.base_url', 'https://img.imageboss.me');

        if (blank($source) || blank(config('statamic.imageboss.api_key'))) {
            return;
        }

        $asset = $event->asset;

        if ($event instanceof AssetSaved) {
            $original = $asset->getOriginal('path');
            if (! $original || $original === $asset->path()) {
                return;
            }

            PurgeImageBossCacheJob::dispatch("{$baseUrl}/{$source}/".OriginPath::for($asset, $original));
        }

        PurgeImageBossCacheJob::dispatch("{$baseUrl}/{$source}/".OriginPath::for($asset));
    }
}
