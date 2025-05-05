<?php

use App\Enums\Bank;
use App\Enums\WebhookStatus;
use App\Jobs\ProcessWebhook;
use App\Models\Client;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

it('test command executed successfully', closure: function () {
    Webhook::factory(20)->create([
        'client_id' => Client::factory()->create()->id,
        'raw_data' => '20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81',
        'bank_name' => Bank::FOODICS,
        'status' => WebhookStatus::PENDING,
    ]);

    artisan('app:process-pending-webhooks')->assertSuccessful();
});

it('triggers process webhook job 20 times for 20 pending webhooks', closure: function () {
    Queue::fake();

    Webhook::factory(20)->create([
        'client_id' => Client::factory()->create()->id,
        'raw_data' => '20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81',
        'bank_name' => Bank::FOODICS,
        'status' => WebhookStatus::PENDING,
    ]);

    artisan('app:process-pending-webhooks')->assertSuccessful();

    Queue::assertPushed(ProcessWebhook::class, 20);
});
