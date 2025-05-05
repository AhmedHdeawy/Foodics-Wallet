<?php

namespace App\Providers;

use App\Repositories\Client\Concretes\ClientRepository;
use App\Repositories\Client\Contracts\ClientRepositoryContract;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repository bindings here
        $this->app->bind(ClientRepositoryContract::class, ClientRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
