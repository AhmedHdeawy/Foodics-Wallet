<?php

namespace App\Jobs;

use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $webhookId)
    {
    }

    /**
     * Execute the job.
     *
     * @throws Throwable
     */
    public function handle(WebhookServiceContract $webhookService): void
    {
        if (config('app.ingestion_paused')) {
            return;
        }

        $webhookService->processWebhook($this->webhookId);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("Failed to process webhook $this->webhookId", [
            'exception' => $exception->getMessage(),
            'error' => $exception->getTraceAsString(),
        ]);
    }
}
