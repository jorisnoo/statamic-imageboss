<?php

use Illuminate\Support\Facades\Bus;
use Noo\StatamicImageboss\Jobs\PurgeImageBossCacheJob;
use Noo\StatamicImageboss\Listeners\PurgeAssetFromImageBoss;
use Statamic\Events\AssetReuploaded;

beforeEach(function () {
    config()->set('statamic.imageboss.source', 'test-source');
    config()->set('statamic.imageboss.api_key', 'test-api-key');
    config()->set('statamic.imageboss.base_url', 'https://img.imageboss.me');

    Bus::fake();
});

it('dispatches purge job when asset is reuploaded', function () {
    $asset = createMockAsset(
        path: '/images/photo.jpg',
        diskHandle: 'assets',
        containerHandle: 'images',
    );

    $listener = new PurgeAssetFromImageBoss;
    $listener->handle(new AssetReuploaded($asset, 'photo.jpg'));

    Bus::assertDispatched(PurgeImageBossCacheJob::class, function ($job) {
        return $job->url === 'https://img.imageboss.me/test-source/assets/images/photo.jpg';
    });
});

it('uses container handle when disk name is null', function () {
    $asset = createMockAsset(path: '/photo.jpg', diskHandle: null, containerHandle: 'uploads');

    $listener = new PurgeAssetFromImageBoss;
    $listener->handle(new AssetReuploaded($asset, 'photo.jpg'));

    Bus::assertDispatched(PurgeImageBossCacheJob::class, function ($job) {
        return $job->url === 'https://img.imageboss.me/test-source/uploads/photo.jpg';
    });
});

it('sanitizes backslashes in asset path', function () {
    $asset = createMockAsset(path: '\\images\\photo.jpg');

    $listener = new PurgeAssetFromImageBoss;
    $listener->handle(new AssetReuploaded($asset, 'photo.jpg'));

    Bus::assertDispatched(PurgeImageBossCacheJob::class, function ($job) {
        return $job->url === 'https://img.imageboss.me/test-source/assets/images/photo.jpg';
    });
});

it('sanitizes double dots in asset path', function () {
    $asset = createMockAsset(path: '/images/../photo.jpg');

    $listener = new PurgeAssetFromImageBoss;
    $listener->handle(new AssetReuploaded($asset, 'photo.jpg'));

    Bus::assertDispatched(PurgeImageBossCacheJob::class, function ($job) {
        return $job->url === 'https://img.imageboss.me/test-source/assets/images/photo.jpg';
    });
});

it('collapses multiple slashes in asset path', function () {
    $asset = createMockAsset(path: '/images///photo.jpg');

    $listener = new PurgeAssetFromImageBoss;
    $listener->handle(new AssetReuploaded($asset, 'photo.jpg'));

    Bus::assertDispatched(PurgeImageBossCacheJob::class, function ($job) {
        return $job->url === 'https://img.imageboss.me/test-source/assets/images/photo.jpg';
    });
});
