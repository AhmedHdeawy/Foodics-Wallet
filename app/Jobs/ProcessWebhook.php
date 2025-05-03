<?php

namespace App\Jobs;

use App\Models\Webhook;
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
    public function __construct(protected Webhook $webhook) {}

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

        $webhookService->processWebhook($this->webhook);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->webhook->markAsFailed($exception->getMessage());

        Log::error("Failed to process webhook {$this->webhook->id}", [
            'exception' => $exception->getMessage(),
            'error' => $exception->getTraceAsString(),
        ]);
    }
}
