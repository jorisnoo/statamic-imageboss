<?php

namespace Noo\StatamicImageboss;

use Noo\StatamicImageboss\Tags\ImagebossTag;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Statamic\Statamic;

class StatamicImagebossServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('statamic-imageboss')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ImageBoss::class);
    }

    public function packageBooted(): void
    {
        Statamic::tag('imageboss', ImagebossTag::class);
    }
}
