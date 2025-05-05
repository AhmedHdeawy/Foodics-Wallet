<?php

namespace App\Providers;

use App\Repositories\Client\Concretes\ClientRepository;
use App\Repositories\Client\Contracts\ClientRepositoryContract;
use App\Repositories\Transaction\Concretes\TransactionRepository;
use App\Repositories\Transaction\Contracts\TransactionRepositoryContract;
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
        $this->app->bind(TransactionRepositoryContract::class, TransactionRepository::class);
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
