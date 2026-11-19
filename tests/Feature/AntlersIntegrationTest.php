<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Noo\StatamicImageboss\Listeners\PurgeAssetFromImageBoss;
use Noo\StatamicImageboss\StatamicImagebossServiceProvider;
use Noo\StatamicImageboss\Tags\ImagebossTag;
use Statamic\Assets\AssetContainer;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades\Antlers;

it('renders the registered Antlers tag with a real container and mapped disk path', function () {
    Storage::fake('public');
    config(['statamic.imageboss.source' => 'test-source', 'statamic.imageboss.path_prefixes' => ['photos' => 'storage']]);
    $container = (new AssetContainer)->handle('photos')->disk('public');
    $asset = $container->makeAsset('summer trip/photo..1.jpg');

    expect(app('statamic.tags')->get('imageboss'))->toBe(ImagebossTag::class);
    $rendered = (string) Antlers::parse('{{ imageboss:url src="photo" width="400" }}', ['photo' => $asset], true);

    expect($rendered)->toBe('https://img.imageboss.me/test-source/width/400/fp-x:0.5,fp-y:0.5,format:auto/storage/summer%20trip/photo..1.jpg');
});

it('accepts asset queries and collections without treating objects as context keys', function () {
    config(['statamic.imageboss.source' => 'test-source']);
    $asset = createMockAsset();
    $query = Mockery::mock(OrderedQueryBuilder::class);
    $query->shouldReceive('first')->once()->andReturn($asset);
    $tag = (new ImagebossTag)->setContext([]);

    expect($tag->setParameters(['src' => $query])->url())->toEndWith('/assets/test.jpg')
        ->and($tag->setParameters(['src' => collect([$asset])])->url())->toEndWith('/assets/test.jpg')
        ->and($tag->setParameters(['src' => collect()])->url())->toBe('')
        ->and($tag->setParameters(['src' => new stdClass])->url())->toBe('');
});

it('registers lifecycle listeners only when purging is configured', function (?string $source, ?string $key, bool $enabled) {
    config(['statamic.imageboss.source' => $source, 'statamic.imageboss.api_key' => $key]);
    (new StatamicImagebossServiceProvider(app()))->packageBooted();

    $listeners = Event::getRawListeners();
    foreach ([AssetUploaded::class, AssetReuploaded::class, AssetDeleted::class, AssetSaved::class] as $event) {
        expect(in_array(PurgeAssetFromImageBoss::class, $listeners[$event] ?? [], true))->toBe($enabled);
    }
})->with([[null, 'key', false], ['source', null, false], ['source', 'key', true]]);
