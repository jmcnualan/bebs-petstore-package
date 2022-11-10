<?php

namespace Bebs\Petstore;

use Bebs\Petstore\PetStore;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->bind(PetStore::class, function ($app) {
            $config = $app['config']['bebs_petstore'];
            return new Client($config);
        });
    }
}
