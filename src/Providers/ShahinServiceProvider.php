<?php

namespace Mrmr7\LaravelShahin\Providers;

use Mrmr7\LaravelShahin\Contracts\TokenStorageInterface;
use Mrmr7\LaravelShahin\ShahinManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ShahinServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-shahin')
            ->hasConfigFile();
    }

    public function packageBooted()
    {
        $this->app->singleton(TokenStorageInterface::class, function () {
            $storageClass = config('shahin.token_storage_class');

            return $this->app->make($storageClass);
        });

        $this->app->singleton('MrMr7/LaravelShahin/Shahin', function ($app) {
            return new ShahinManager;
        });
    }
}
