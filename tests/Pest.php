<?php

use Noo\StatamicImageboss\Tests\TestCase;

uses(TestCase::class)->in('Feature');

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

    $asset = Mockery::mock(Statamic\Assets\Asset::class);
    $asset->shouldReceive('container')->andReturn($container);
    $asset->shouldReceive('data')->andReturn($data);
    $asset->shouldReceive('path')->andReturn('/test.jpg');

    return $asset;
}
