<?php

namespace Noo\StatamicImageboss\Tests;

use Noo\StatamicImageboss\StatamicImagebossServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            StatamicImagebossServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('statamic-imageboss.source', null);
        $app['config']->set('statamic-imageboss.secret', null);
        $app['config']->set('statamic-imageboss.base_url', 'https://img.imageboss.me');
        $app['config']->set('statamic-imageboss.default_width', 1000);
        $app['config']->set('statamic-imageboss.width_interval', 320);
        $app['config']->set('statamic-imageboss.presets', [
            'default' => ['min' => 320, 'max' => 2560],
            'thumbnail' => ['min' => 200, 'max' => 700, 'ratio' => 1, 'interval' => 250],
            'card' => ['min' => 300, 'max' => 800, 'ratio' => 0.8],
            'hero' => ['min' => 640, 'max' => 3840],
        ]);
    }
}
