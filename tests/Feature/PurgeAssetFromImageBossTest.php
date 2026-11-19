<?php

use Illuminate\Support\Facades\Bus;
use Noo\StatamicImageboss\Jobs\PurgeImageBossCacheJob;
use Noo\StatamicImageboss\Listeners\PurgeAssetFromImageBoss;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

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

it('purges old and new paths on a move and ignores metadata-only saves', function () {
    $asset = createMockAsset(path: 'new.jpg');
    $asset->shouldReceive('getOriginal')->with('path')->andReturn('old.jpg', 'new.jpg');
    $listener = new PurgeAssetFromImageBoss;
    $listener->handle(new AssetSaved($asset));
    $listener->handle(new AssetSaved($asset));

    Bus::assertDispatchedTimes(PurgeImageBossCacheJob::class, 2);
    foreach (['old.jpg', 'new.jpg'] as $path) {
        Bus::assertDispatched(PurgeImageBossCacheJob::class, fn ($job) => str_ends_with($job->url, '/assets/'.$path));
    }
});

it('purges uploads and deletions using the encoded configured prefix', function (string $event) {
    config(['statamic.imageboss.path_prefixes' => ['assets' => 'storage']]);
    $asset = createMockAsset(path: 'photo #1.jpg');
    (new PurgeAssetFromImageBoss)->handle($event === AssetDeleted::class ? new AssetDeleted($asset) : new AssetUploaded($asset, 'photo #1.jpg'));

    Bus::assertDispatched(PurgeImageBossCacheJob::class, fn ($job) => str_ends_with($job->url, '/storage/photo%20%231.jpg'));
})->with([AssetDeleted::class, AssetUploaded::class]);

it('does not enqueue purges without both credentials', function (string $missing) {
    config(['statamic.imageboss.'.$missing => null]);
    (new PurgeAssetFromImageBoss)->handle(new AssetDeleted(createMockAsset()));

    Bus::assertNothingDispatched();
})->with(['source', 'api_key']);
