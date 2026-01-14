<?php

use Noo\StatamicImageboss\ImageBoss;
use Noo\StatamicImageboss\ImageBossBuilder;

enum TestPreset: string
{
    case Card = 'card';
    case Thumbnail = 'thumbnail';
}

beforeEach(function () {
    config()->set('statamic-imageboss.source', 'test-source');
    config()->set('statamic-imageboss.secret', null);
    config()->set('statamic-imageboss.base_url', 'https://img.imageboss.me');
    config()->set('statamic-imageboss.default_width', 1000);
    config()->set('statamic-imageboss.width_interval', 320);
    config()->set('statamic-imageboss.presets', [
        'default' => ['min' => 320, 'max' => 2560],
        'thumbnail' => ['min' => 200, 'max' => 700, 'ratio' => 1, 'interval' => 250],
        'card' => ['min' => 300, 'max' => 800, 'ratio' => 0.8],
        'hero' => ['min' => 640, 'max' => 3840],
    ]);
});

function createMockAsset(bool $hasFocus = false, ?string $focusValue = null): Mockery\MockInterface
{
    $disk = Mockery::mock();
    $disk->name = 'assets';

    $container = Mockery::mock();
    $container->shouldReceive('disk')->andReturn($disk);
    $container->shouldReceive('handle')->andReturn('assets');

    $data = Mockery::mock();
    $data->shouldReceive('has')->with('focus')->andReturn($hasFocus);

    if ($hasFocus && $focusValue) {
        $data->shouldReceive('get')->with('focus')->andReturn($focusValue);
    }

    $asset = Mockery::mock(\Statamic\Assets\Asset::class);
    $asset->shouldReceive('container')->andReturn($container);
    $asset->shouldReceive('data')->andReturn($data);
    $asset->shouldReceive('path')->andReturn('/test.jpg');

    return $asset;
}

it('can be instantiated via the factory', function () {
    $imageBoss = new ImageBoss;

    expect($imageBoss)->toBeInstanceOf(ImageBoss::class);
});

it('loads preset configuration', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset('card');

    $srcset = $builder->srcset();

    expect($srcset)->toBeArray();
    expect($srcset[0]['width'])->toBe(300);
    expect(end($srcset)['width'])->toBe(800);
});

it('generates correct widths with default interval', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->min(300)->max(900);

    $srcset = $builder->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths)->toBe([300, 620, 900]);
});

it('generates correct widths with custom interval', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->min(200)->max(600)->interval(200);

    $srcset = $builder->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths)->toBe([200, 400, 600]);
});

it('always includes max width in srcset', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->min(300)->max(700)->interval(200);

    $srcset = $builder->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths)->toBe([300, 500, 700]);
});

it('generates srcset string format', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->min(300)->max(500)->interval(200);

    $srcsetString = $builder->srcsetString();

    expect($srcsetString)->toContain('300w');
    expect($srcsetString)->toContain('500w');
    expect($srcsetString)->toContain(', ');
});

it('generates imageboss url with width operation', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('https://img.imageboss.me');
    expect($url)->toContain('test-source');
    expect($url)->toContain('width/800');
    expect($url)->toContain('format:auto');
});

it('generates imageboss url with cover operation when height is set', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800)->height(600);

    $url = $builder->url();

    expect($url)->toContain('cover/800x600');
});

it('calculates height from ratio', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800)->ratio(16 / 9);

    $url = $builder->url();

    expect($url)->toContain('cover/800x450');
});

it('includes focal point in url', function () {
    $asset = createMockAsset(true, '25-75');

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('fp-x:0.3,fp-y:0.8');
});

it('signs url when secret is configured', function () {
    config()->set('statamic-imageboss.secret', 'test-secret');

    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('?bossToken=');
});

it('uses default width from config when no width specified', function () {
    config()->set('statamic-imageboss.default_width', 500);

    $asset = createMockAsset();

    $builder = new ImageBossBuilder($asset);

    $url = $builder->url();

    expect($url)->toContain('width/500');
});

it('accepts backed enum for type-safe preset selection', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset(TestPreset::Card);

    $srcset = $builder->srcset();

    expect($srcset)->toBeArray();
    expect($srcset[0]['width'])->toBe(300);
    expect(end($srcset)['width'])->toBe(800);
});

it('applies ratio from preset', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset('thumbnail')->width(400);

    $url = $builder->url();

    expect($url)->toContain('cover/400x400');
});

it('applies interval from preset', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset('thumbnail');

    $srcset = $builder->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths)->toBe([200, 450, 700]);
});
