<?php

namespace Noo\StatamicImageboss;

use Illuminate\Support\Facades\Event;
use Noo\StatamicImageboss\Listeners\PurgeAssetFromImageBoss;
use Noo\StatamicImageboss\Tags\ImagebossTag;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Statamic\Events\AssetReuploaded;
use Statamic\Statamic;

class StatamicImagebossServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('statamic-imageboss');
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic/imageboss.php', 'statamic.imageboss');

        $this->app->singleton(ImageBoss::class);
    }

    public function packageBooted(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/statamic/imageboss.php' => config_path('statamic/imageboss.php'),
            ], 'imageboss-config');
        }

        Statamic::tag('imageboss', ImagebossTag::class);

        if (filled(config('statamic.imageboss.api_key'))) {
            Event::listen(AssetReuploaded::class, PurgeAssetFromImageBoss::class);
        }
    }
}
