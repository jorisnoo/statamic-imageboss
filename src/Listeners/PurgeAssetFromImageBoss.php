<?php

namespace Noo\StatamicImageboss\Listeners;

use Noo\StatamicImageboss\Jobs\PurgeImageBossCacheJob;
use Statamic\Events\AssetReuploaded;

class PurgeAssetFromImageBoss
{
    public function handle(AssetReuploaded $event): void
    {
        $source = config('statamic.imageboss.source');
        $baseUrl = config('statamic.imageboss.base_url', 'https://img.imageboss.me');

        $diskName = $event->asset->container()->diskHandle()
            ?? $event->asset->container()->handle();

        $path = $this->sanitizePath($event->asset->path());

        PurgeImageBossCacheJob::dispatch("{$baseUrl}/{$source}/{$diskName}/{$path}");
    }

    private function sanitizePath(string $path): string
    {
        $path = str_replace(['\\', '..'], ['/', ''], $path);
        $path = (string) preg_replace('#/+#', '/', $path);

        return ltrim($path, '/');
    }
}
