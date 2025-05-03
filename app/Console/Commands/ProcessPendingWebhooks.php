<?php

namespace App\Console\Commands;

use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ProcessPendingWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-pending-webhooks {--limit=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending webhooks that were not processed yet';

    /**
     * Execute the console command.
     */
    public function handle(WebhookServiceContract $webhookService): int
    {
        if (config('app.ingestion_paused')) {
            return CommandAlias::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        $webhookService->processPendingWebhooks($limit);

        return CommandAlias::SUCCESS;
    }
}
