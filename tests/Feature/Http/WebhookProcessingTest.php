<?php

use App\Enums\Bank;
use App\Jobs\ProcessWebhook;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\call;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->validFoodicsSingleTransaction = '20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81';
    $this->validAcmeSingleTransaction = '210,23//202504159000001//20250415';
});

function webhookRequest(?string $payload, ?int $clientId = null, string $bank = Bank::FOODICS->value): TestResponse
{
    return call(
        'POST',
        apiRoute('webhooks/'.$bank),
        [],
        [],
        [],
        [
            'HTTP_X_CLIENT_ID' => $clientId,
            'HTTP_ACCEPT' => 'application/json',
        ],
        $payload,
    );
}

it('successfully receives webhook from foodics bank', function () {
    $response = webhookRequest($this->validFoodicsSingleTransaction, $this->client->id);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'webhook_id',
                'message',
            ],
        ]);

    assertDatabaseHas('webhooks', [
        'client_id' => $this->client->id,
        'bank_name' => Bank::FOODICS->value,
        'raw_data' => $this->validFoodicsSingleTransaction,
    ]);

    assertDatabaseHas('clients', [
        'id' => $this->client->id,
        'balance' => 156.50,
    ]);

    assertDatabaseCount('transactions', 1);
});

it('successfully receives webhook from acme bank', function () {
    $response = webhookRequest($this->validAcmeSingleTransaction, $this->client->id, Bank::ACME->value);
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'webhook_id',
                'message',
            ],
        ]);

    assertDatabaseHas('webhooks', [
        'client_id' => $this->client->id,
        'bank_name' => Bank::ACME->value,
        'raw_data' => $this->validAcmeSingleTransaction,
    ]);
    assertDatabaseHas('clients', [
        'id' => $this->client->id,
        'balance' => 210.23,
    ]);

    assertDatabaseCount('transactions', 1);
});

it('triggers the process webhook job after receiving the webhook request', function () {
    Queue::fake();
    $response = webhookRequest($this->validAcmeSingleTransaction, $this->client->id, Bank::ACME->value);

    $webhookId = $response->json('data.webhook_id');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'webhook_id',
                'message',
            ],
        ]);

    assertDatabaseHas('webhooks', [
        'client_id' => $this->client->id,
        'bank_name' => Bank::ACME->value,
        'raw_data' => $this->validAcmeSingleTransaction,
    ]);

    Queue::assertPushed(ProcessWebhook::class, function ($job) use ($webhookId) {
        return $job->webhookId === $webhookId;
    });
});

it('fails with invalid bank', function () {
    $response = webhookRequest($this->validFoodicsSingleTransaction, $this->client->id, 'zero_bank');

    $response->assertStatus(404)->assertJson([
        'message' => 'Case [zero_bank] not found on Backed Enum [App\\Enums\\Bank].',
    ]);

    assertDatabaseCount('webhooks', 0);
});

it('requires valid client header', function () {
    $response = webhookRequest($this->validFoodicsSingleTransaction);

    $response->assertStatus(422)
        ->assertJsonValidationErrorFor('client_id');

    assertDatabaseCount('webhooks', 0);
});

it('fails with empty content', function () {
    $response = webhookRequest(null, $this->client->id);

    $response->assertStatus(422)
        ->assertJsonValidationErrorFor('content');

    assertDatabaseCount('webhooks', 0);
});

it('does not trigger the job when an error returned', function () {
    Queue::fake();
    $response = webhookRequest($this->validFoodicsSingleTransaction, $this->client->id, 'zero_bank');

    $response->assertStatus(404)->assertJson([
        'message' => 'Case [zero_bank] not found on Backed Enum [App\\Enums\\Bank].',
    ]);

    assertDatabaseCount('webhooks', 0);
    Queue::assertNotPushed(ProcessWebhook::class);
});

it('handles multiple foodics transaction in one webhook', function () {
    $multiLineWebhookData = "20250615156,50#202506159000001#note/payment1\n".
        '20250616200,00#202506169000002#note/payment2';

    $response = webhookRequest($multiLineWebhookData, $this->client->id);

    $response->assertStatus(200);

    assertDatabaseHas('webhooks', [
        'client_id' => $this->client->id,
        'bank_name' => Bank::FOODICS->value,
        'raw_data' => $multiLineWebhookData,
    ]);
});

it('handles multiple acme transaction in one webhook', function () {
    $multiLineWebhookData = "156,50//202504159000001//20250415\n".
        '7623,88//2024110556873465//20241105';

    $response = webhookRequest($multiLineWebhookData, $this->client->id, Bank::ACME->value);

    $response->assertStatus(200);

    assertDatabaseHas('webhooks', [
        'client_id' => $this->client->id,
        'bank_name' => Bank::ACME->value,
        'raw_data' => $multiLineWebhookData,
    ]);
});

it('webhooks rate limiting remaining and limit', function () {
    $maxAttempt = config('app.webhook_rate_limit.max_requests');

    webhookRequest($this->validFoodicsSingleTransaction, $this->client->id)
        ->assertStatus(200)->assertHeader('x-ratelimit-remaining', $maxAttempt - 1)
        ->assertHeader('x-ratelimit-limit', $maxAttempt);
});

it('enforces rate limiting for webhooks', function () {
    $maxAttempt = config('app.webhook_rate_limit.max_requests');
    for ($i = 0; $i < $maxAttempt; $i++) {
        webhookRequest($this->validFoodicsSingleTransaction, $this->client->id)->assertStatus(200);
    }

    webhookRequest($this->validFoodicsSingleTransaction, $this->client->id)->assertStatus(429);
});
