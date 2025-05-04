<?php

use App\Enums\Bank;
use App\Enums\WebhookStatus;
use App\Jobs\ProcessWebhook;
use App\Models\Client;
use App\Models\Webhook;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create(['id' => 1]);
});

it('successfully handles foodics webhook', closure: function () {
    $foodicsWebhook = Webhook::factory()->create([
        'client_id' => $this->client->id,
        'raw_data' => '20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81',
        'bank_name' => Bank::FOODICS,
        'status' => WebhookStatus::PENDING
    ]);

    $job = new ProcessWebhook($foodicsWebhook->id);

    $mockService = Mockery::mock(WebhookServiceContract::class);
    $mockService->shouldReceive('processWebhook')
        ->once()
        ->with($foodicsWebhook->id);

    $job->handle($mockService);
});

it('successfully handles acme webhook', function () {
    $acmeWebhook = Webhook::factory()->create([
        'client_id' => $this->client->id,
        'raw_data' => '156,50//202504159000001//20250415',
        'bank_name' => Bank::ACME,
        'status' => WebhookStatus::PENDING
    ]);

    $job = new ProcessWebhook($acmeWebhook->id);

    $mockService = Mockery::mock(WebhookServiceContract::class);
    $mockService->shouldReceive('processWebhook')
        ->once()
        ->with($acmeWebhook->id);

    $job->handle($mockService);
});
