<?php

namespace App\Providers;

use App\Services\Clients\Concretes\ClientService;
use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transactions\Concretes\TransactionService;
use App\Services\Transactions\Contracts\TransactionServiceContract;
use App\Services\Transfers\Concretes\TransferService;
use App\Services\Transfers\Contracts\TransferServiceContract;
use App\Services\TransferXmlBuilder\Concretes\TransferXmlBuilder;
use App\Services\TransferXmlBuilder\Contracts\TransferXmlBuilderContract;
use App\Services\Webhooks\Concretes\WebhookService;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WebhookServiceContract::class, WebhookService::class);
        $this->app->bind(TransactionServiceContract::class, TransactionService::class);
        $this->app->bind(TransferServiceContract::class, TransferService::class);
        $this->app->bind(TransferXmlBuilderContract::class, TransferXmlBuilder::class);
        $this->app->bind(ClientServiceContract::class, ClientService::class);

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the webhook rate limiter
        $this->webhookRateLimiter();

        // Register the transfer rate limiter
        $this->transferRateLimiter();
    }

    public function webhookRateLimiter(): void
    {
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinutes(
                decayMinutes: config('app.webhook_rate_limit.time_window'),
                maxAttempts: config('app.webhook_rate_limit.max_requests'),
            )->by($request->route('bank'));
        });
    }

    public function transferRateLimiter(): void
    {
        RateLimiter::for('transfer', function (Request $request) {
            // Later, We can improve this by using the bank name or client id.
            return Limit::perMinutes(
                decayMinutes: config('app.transfer_rate_limit.time_window'),
                maxAttempts: config('app.transfer_rate_limit.max_requests'),
            )->by($request->ip());
        });
    }
}
