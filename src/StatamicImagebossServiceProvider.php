<?php

namespace Noo\StatamicImageboss;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Noo\StatamicImageboss\Commands\StatamicImagebossCommand;

class StatamicImagebossServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('statamic-imageboss')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_statamic_imageboss_table')
            ->hasCommand(StatamicImagebossCommand::class);
    }
}
