<?php

use Mockery\MockInterface;
use Noo\StatamicImageboss\Tests\TestCase;
use Statamic\Assets\Asset;
use Statamic\Facades\Image;

uses(TestCase::class)->in('Feature');

function createMockAsset(
    bool $hasFocus = false,
    mixed $focusValue = null,
    ?int $width = null,
    ?int $height = null,
    string $path = '/test.jpg',
    ?string $diskHandle = 'assets',
    string $containerHandle = 'assets',
): MockInterface {
    $container = Mockery::mock();
    $container->shouldReceive('diskHandle')->andReturn($diskHandle);
    $container->shouldReceive('handle')->andReturn($containerHandle);

    $data = Mockery::mock();
    $data->shouldReceive('has')->with('focus')->andReturn($hasFocus);

    if ($hasFocus && $focusValue !== null) {
        $data->shouldReceive('get')->with('focus')->andReturn($focusValue);
    }

    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('container')->andReturn($container);
    $asset->shouldReceive('data')->andReturn($data);
    $asset->shouldReceive('path')->andReturn($path);

    if ($width !== null) {
        $asset->shouldReceive('width')->andReturn($width);
    }

    if ($height !== null) {
        $asset->shouldReceive('height')->andReturn($height);
    }

    return $asset;
}

function createGlideMock(array $extraMethods = []): MockInterface
{
    config()->set('statamic.imageboss.source', null);

    $manipulation = Mockery::mock();
    $manipulation->shouldReceive('width')->andReturn($manipulation);
    $manipulation->shouldReceive('build')->andReturn('/glide/test.jpg');

    foreach ($extraMethods as $method => $args) {
        $expectation = $manipulation->shouldReceive($method);

        if ($args !== null) {
            $expectation->with($args);
        }

        $expectation->andReturn($manipulation);
    }

    Image::shouldReceive('manipulate')->andReturn($manipulation);

    return $manipulation;
}
