<?php

namespace App\Providers;

use App\Services\Transactions\Concretes\TransactionService;
use App\Services\Transactions\Contracts\TransactionServiceContract;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the webhook rate limiter
        $this->webhookRateLimiter();
    }

    public function webhookRateLimiter(): void
    {
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(500)->by($request->route('bank'));
        });
    }
}
