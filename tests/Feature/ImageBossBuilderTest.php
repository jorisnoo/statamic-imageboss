<?php

use Noo\StatamicImageboss\ImageBoss;
use Noo\StatamicImageboss\ImageBossBuilder;
use Noo\StatamicImageboss\Tests\Fixtures\InterfacePreset;
use Noo\StatamicImageboss\Tests\Fixtures\TestPreset;

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

it('can be instantiated via the factory', function () {
    $imageBoss = new ImageBoss;

    expect($imageBoss)->toBeInstanceOf(ImageBoss::class);
});

it('loads preset configuration', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset('card');

    $srcset = $builder->srcset();

    expect($srcset)->toBeArray()
        ->and($srcset[0]['width'])->toBe(300)
        ->and(end($srcset)['width'])->toBe(800);
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

    expect($srcsetString)->toContain('300w')
        ->and($srcsetString)->toContain('500w')
        ->and($srcsetString)->toContain(', ');
});

it('generates imageboss url with width operation', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('https://img.imageboss.me')
        ->and($url)->toContain('test-source')
        ->and($url)->toContain('width/800')
        ->and($url)->toContain('format:auto');
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

    expect($srcset)->toBeArray()
        ->and($srcset[0]['width'])->toBe(300)
        ->and(end($srcset)['width'])->toBe(800);
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

it('accepts interface-based preset without config lookup', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset(InterfacePreset::Custom);

    $srcset = $builder->srcset();

    expect($srcset)->toBeArray()
        ->and($srcset[0]['width'])->toBe(100)
        ->and(end($srcset)['width'])->toBe(500);
});

it('applies ratio from interface-based preset', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset(InterfacePreset::WithRatio)->width(400);

    $url = $builder->url();

    expect($url)->toContain('cover/400x200');
});

it('applies interval from interface-based preset', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset(InterfacePreset::WithInterval);

    $srcset = $builder->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths)->toBe([150, 300, 450]);
});

it('interface preset takes precedence over config lookup', function () {
    config()->set('statamic-imageboss.presets.custom', ['min' => 999, 'max' => 9999]);

    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset(InterfacePreset::Custom);

    $srcset = $builder->srcset();

    expect($srcset[0]['width'])->toBe(100)
        ->and(end($srcset)['width'])->toBe(500);
});

it('provides helper methods via trait', function () {
    expect(InterfacePreset::Custom->min())->toBe(100)
        ->and(InterfacePreset::Custom->max())->toBe(500)
        ->and(InterfacePreset::Custom->ratio())->toBeNull()
        ->and(InterfacePreset::Custom->interval())->toBeNull()
        ->and(InterfacePreset::WithRatio->ratio())->toBe(2.0)
        ->and(InterfacePreset::WithInterval->interval())->toBe(150);
});

it('ignores null values passed to setter methods', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))
        ->width(800)
        ->height(null)
        ->ratio(null)
        ->min(300)
        ->max(null)
        ->interval(null);

    $url = $builder->url();

    expect($url)->toContain('width/800')
        ->and($url)->not->toContain('cover/');

    $srcset = $builder->srcset();

    expect($srcset[0]['width'])->toBe(300);
});

it('returns explicit ratio from aspectRatio()', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->ratio(16 / 9);

    expect($builder->aspectRatio())->toBe(16 / 9);
});

it('calculates aspectRatio() from width and height', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800)->height(600);

    expect($builder->aspectRatio())->toBe(800 / 600);
});

it('returns null from aspectRatio() when insufficient data', function () {
    $asset = createMockAsset();

    $builder = new ImageBossBuilder($asset);

    expect($builder->aspectRatio())->toBeNull();

    $builderWithWidth = (new ImageBossBuilder($asset))->width(800);

    expect($builderWithWidth->aspectRatio())->toBeNull();

    $builderWithHeight = (new ImageBossBuilder($asset))->height(600);

    expect($builderWithHeight->aspectRatio())->toBeNull();
});

it('prefers explicit ratio over calculated ratio in aspectRatio()', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))
        ->width(800)
        ->height(600)
        ->ratio(16 / 9);

    expect($builder->aspectRatio())->toBe(16 / 9);
});
