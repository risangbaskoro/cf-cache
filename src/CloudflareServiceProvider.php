<?php

namespace Baskoro\CloudflareCache;

use Baskoro\CloudflareCache\Cache\CloudflareCacheConnector;
use Baskoro\CloudflareCache\Cache\CloudflareKvStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CloudflareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booting(function () {
            Cache::extend('cloudflare', function (Application $app, array $config) {
                return Cache::repository(new CloudflareKvStore(
                    new CloudflareCacheConnector($config['token'], $config['account_id'], $config['namespace_id']),
                    prefix: $app['config']['cache.prefix']
                ));
            });
        });
    }

    public function boot(): void
    {
        //
    }
}
