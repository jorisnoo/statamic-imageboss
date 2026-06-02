<?php

use Noo\StatamicImageboss\ImageBoss;
use Noo\StatamicImageboss\ImageBossBuilder;
use Noo\StatamicImageboss\NullImageBossBuilder;
use Noo\StatamicImageboss\Tests\Fixtures\InterfacePreset;
use Noo\StatamicImageboss\Tests\Fixtures\TestPreset;

beforeEach(function () {
    config()->set('statamic.imageboss.source', 'test-source');
    config()->set('statamic.imageboss.secret', null);
    config()->set('statamic.imageboss.base_url', 'https://img.imageboss.me');
    config()->set('statamic.imageboss.default_width', 1000);
    config()->set('statamic.imageboss.width_interval', 320);
    config()->set('statamic.imageboss.presets', [
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

it('includes animation:true in url when animation is enabled', function () {
    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->width(300)->animation()->url();

    expect($url)->toContain('animation:true');
});

it('omits animation from url by default', function () {
    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->width(300)->url();

    expect($url)->not->toContain('animation:');
});

it('omits animation from url when animation is false or null', function () {
    $asset = createMockAsset();

    $urlFalse = (new ImageBossBuilder($asset))->width(300)->animation(false)->url();
    $urlNull = (new ImageBossBuilder($asset))->width(300)->animation(null)->url();

    expect($urlFalse)->not->toContain('animation:')
        ->and($urlNull)->not->toContain('animation:');
});

it('includes animation:true in rias url when animation is enabled', function () {
    $asset = createMockAsset(path: '/test.jpg');

    $rias = (new ImageBossBuilder($asset))->animation()->rias();

    expect($rias)->toContain('animation:true')
        ->and($rias)->toContain('width/{width}');
});

it('applies animation from preset configuration', function () {
    config()->set('statamic.imageboss.presets.gif', ['min' => 300, 'max' => 300, 'animation' => true]);

    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->preset('gif')->width(300)->url();

    expect($url)->toContain('animation:true');
});

it('includes focal point in url', function () {
    $asset = createMockAsset(true, '25-75-1');

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('fp-x:0.3,fp-y:0.8');
});

it('includes focal point from two-part format in url', function () {
    $asset = createMockAsset(true, '25-75');

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('fp-x:0.3,fp-y:0.8');
});

it('signs url when secret is configured', function () {
    config()->set('statamic.imageboss.secret', 'test-secret');

    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->width(800);

    $url = $builder->url();

    expect($url)->toContain('?bossToken=');
});

it('uses default width from config when no width specified', function () {
    config()->set('statamic.imageboss.default_width', 500);

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
    config()->set('statamic.imageboss.presets.custom', ['min' => 999, 'max' => 9999]);

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

it('returns NullImageBossBuilder from factory when asset is null', function () {
    $imageBoss = new ImageBoss;

    $result = $imageBoss->from(null);

    expect($result)->toBeInstanceOf(NullImageBossBuilder::class)
        ->and($result->url())->toBe('');
});

it('returns NullImageBossBuilder from factory when Value unwraps to null', function () {
    $value = Mockery::mock(\Statamic\Fields\Value::class);
    $value->shouldReceive('value')->andReturn(null);

    $imageBoss = new ImageBoss;

    $result = $imageBoss->from($value);

    expect($result)->toBeInstanceOf(NullImageBossBuilder::class)
        ->and($result->url())->toBe('');
});

it('returns empty values from null builder', function () {
    $builder = new NullImageBossBuilder;

    expect($builder->url())->toBe('')
        ->and($builder->srcset())->toBe([])
        ->and($builder->srcsetString())->toBe('')
        ->and($builder->rias())->toBe('')
        ->and($builder->aspectRatio())->toBeNull();
});

it('allows chaining on null builder', function () {
    $builder = new NullImageBossBuilder;

    $result = $builder->width(800)->height(600)->ratio(16 / 9)->min(320)->max(2560)->interval(320)->url();

    expect($result)->toBe('');
});

it('generates placeholder with explicit width and height', function () {
    $asset = createMockAsset();

    $placeholder = (new ImageBossBuilder($asset))->width(800)->height(600)->placeholder();

    expect($placeholder)->toBe("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='600'%3E%3C/svg%3E");
});

it('generates placeholder with width and ratio', function () {
    $asset = createMockAsset();

    $placeholder = (new ImageBossBuilder($asset))->width(800)->ratio(16 / 9)->placeholder();

    expect($placeholder)->toBe("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='450'%3E%3C/svg%3E");
});

it('generates placeholder from asset native dimensions', function () {
    $asset = createMockAsset(width: 1920, height: 1080);

    $placeholder = (new ImageBossBuilder($asset))->placeholder();

    expect($placeholder)->toBe("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1920' height='1080'%3E%3C/svg%3E");
});

it('returns empty string for placeholder when dimensions are unresolvable', function () {
    $asset = createMockAsset();
    $asset->shouldReceive('width')->andReturn(null);
    $asset->shouldReceive('height')->andReturn(null);

    $placeholder = (new ImageBossBuilder($asset))->placeholder();

    expect($placeholder)->toBe('');
});

it('returns empty string for placeholder from null builder', function () {
    $builder = new NullImageBossBuilder;

    expect($builder->placeholder())->toBe('');
});

it('generates placeholder starting with data uri prefix', function () {
    $asset = createMockAsset(width: 400, height: 300);

    $placeholder = (new ImageBossBuilder($asset))->placeholder();

    expect($placeholder)->toStartWith('data:image/svg+xml,');
});

// RIAS URL generation

it('generates rias url with width placeholder', function () {
    $asset = createMockAsset();

    $rias = (new ImageBossBuilder($asset))->rias();

    expect($rias)->toContain('test-source')
        ->and($rias)->toContain('width/{width}')
        ->and($rias)->toContain('format:auto')
        ->and($rias)->toContain('test.jpg');
});

it('generates rias url with cover and height placeholder when height is set', function () {
    $asset = createMockAsset();

    $rias = (new ImageBossBuilder($asset))->height(600)->rias();

    expect($rias)->toContain('cover/{width}x{height}');
});

it('generates rias url with cover and height placeholder when ratio is set', function () {
    $asset = createMockAsset();

    $rias = (new ImageBossBuilder($asset))->ratio(16 / 9)->rias();

    expect($rias)->toContain('cover/{width}x{height}');
});

it('includes focal point in rias url', function () {
    $asset = createMockAsset(true, '50-50-1');

    $rias = (new ImageBossBuilder($asset))->rias();

    expect($rias)->toContain('fp-x:0.5,fp-y:0.5');
});

it('falls back to regular url for rias when source is not configured', function () {
    $asset = createMockAsset();

    createGlideMock();
    Statamic\Facades\URL::shouldReceive('makeAbsolute')->andReturn('http://localhost/glide/test.jpg?w=1000');

    $rias = (new ImageBossBuilder($asset))->rias();

    expect($rias)->toContain('glide');
});

// Glide fallback

it('falls back to glide url when source is not configured', function () {
    $asset = createMockAsset();

    createGlideMock();
    Statamic\Facades\URL::shouldReceive('makeAbsolute')->andReturn('http://localhost/glide/test.jpg?w=800');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toBe('http://localhost/glide/test.jpg?w=800');
});

it('falls back to glide url with crop_focal when height is set', function () {
    $asset = createMockAsset();

    createGlideMock(['height' => null, 'fit' => 'crop_focal']);
    Statamic\Facades\URL::shouldReceive('makeAbsolute')->andReturn('http://localhost/glide/test.jpg?w=800&h=600');

    $url = (new ImageBossBuilder($asset))->width(800)->height(600)->url();

    expect($url)->toBe('http://localhost/glide/test.jpg?w=800&h=600');
});

it('generates glide srcset when source is not configured', function () {
    $asset = createMockAsset();

    createGlideMock();
    Statamic\Facades\URL::shouldReceive('makeAbsolute')->andReturn('http://localhost/glide/test.jpg');

    $srcset = (new ImageBossBuilder($asset))->min(300)->max(500)->interval(200)->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths)->toBe([300, 500]);
});

// Focal point edge cases

it('defaults to center when focus data is not a string', function () {
    $asset = createMockAsset(hasFocus: true, focusValue: 123);

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('fp-x:0.5,fp-y:0.5')
        ->and($url)->toContain('format:auto');
});

it('defaults to center when focus string has no dash', function () {
    $asset = createMockAsset(hasFocus: true, focusValue: 'center');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('fp-x:0.5,fp-y:0.5');
});

it('defaults to center when focus has too many parts', function () {
    $asset = createMockAsset(true, '25-75-1-extra');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('fp-x:0.5,fp-y:0.5');
});

it('defaults to center when focus has non-numeric values', function () {
    $asset = createMockAsset(true, 'abc-def');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('fp-x:0.5,fp-y:0.5');
});

it('defaults to center when focus has out-of-range values', function () {
    $asset = createMockAsset(true, '150-50-1');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('fp-x:0.5,fp-y:0.5');
});

it('handles focal point at boundary values 0 and 100', function () {
    $asset = createMockAsset(true, '0-100-1');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('fp-x:0,fp-y:1');
});

// URL signing verification

it('generates correct bossToken signature', function () {
    config()->set('statamic.imageboss.secret', 'my-secret');

    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    $path = '/test-source/width/800/fp-x:0.5,fp-y:0.5,format:auto/assets/test.jpg';
    $expectedToken = hash_hmac('sha256', $path, 'my-secret');

    expect($url)->toContain("?bossToken={$expectedToken}");
});

it('does not append bossToken when secret is null', function () {
    config()->set('statamic.imageboss.secret', null);

    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->not->toContain('bossToken');
});

// Path sanitization

it('sanitizes backslashes in asset path', function () {
    $asset = createMockAsset(path: 'folder\\image.jpg');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('folder/image.jpg')
        ->and($url)->not->toContain('\\');
});

it('sanitizes double dots in asset path', function () {
    $asset = createMockAsset(path: 'folder/../secret/image.jpg');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->not->toContain('..');
});

it('collapses multiple slashes in asset path', function () {
    $asset = createMockAsset(path: 'folder///image.jpg');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('folder/image.jpg')
        ->and($url)->not->toContain('///');
});

// ImageBoss::from() error handling

it('throws InvalidArgumentException for non-asset input', function () {
    $imageBoss = new ImageBoss;

    $imageBoss->from('not-an-asset');
})->throws(\InvalidArgumentException::class);

it('unwraps Value objects before checking asset type', function () {
    $asset = createMockAsset();

    $value = Mockery::mock(\Statamic\Fields\Value::class);
    $value->shouldReceive('value')->andReturn($asset);

    $imageBoss = new ImageBoss;

    $result = $imageBoss->from($value);

    expect($result)->toBeInstanceOf(ImageBossBuilder::class);
});

// Setter edge cases

it('ignores zero width', function () {
    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->width(0)->url();

    expect($url)->toContain('width/1000');
});

it('ignores negative width', function () {
    $asset = createMockAsset();

    $url = (new ImageBossBuilder($asset))->width(-100)->url();

    expect($url)->toContain('width/1000');
});

it('ignores zero ratio', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->ratio(0.0);

    expect($builder->aspectRatio())->toBeNull();
});

it('ignores negative ratio', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->ratio(-1.5);

    expect($builder->aspectRatio())->toBeNull();
});

it('ignores non-existent preset', function () {
    $asset = createMockAsset();

    $builder = (new ImageBossBuilder($asset))->preset('nonexistent');

    $srcset = $builder->srcset();
    $widths = array_column($srcset, 'width');

    expect($widths[0])->toBe(320)
        ->and(end($widths))->toBe(2560);
});

// Container disk name fallback

it('uses container handle when disk name is null', function () {
    $asset = createMockAsset(diskName: null, containerHandle: 'my-handle');

    $url = (new ImageBossBuilder($asset))->width(800)->url();

    expect($url)->toContain('my-handle');
});
