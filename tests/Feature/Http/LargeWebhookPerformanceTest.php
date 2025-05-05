<?php

use App\Enums\Bank;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Benchmark;

use function Pest\Laravel\call;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('app.webhook_rate_limit.max_requests', 120);
    config()->set('queue.default', 'database');
});

afterEach(function () {
    config()->set('app.webhook_rate_limit.max_requests', env('WEBHOOK_RATE_LIMIT_MAX_REQUESTS', 4));
    config()->set('queue.default', 'sync');
});

function getWebhookData(int $max = 10000): string
{
    $lines = [];
    for ($i = 1; $i <= $max; $i++) {
        $date = Carbon\Carbon::now()->subDays(mt_rand(1, 400))->format('Ymd');
        $amount = mt_rand(1000, 9999) / 100;
        $lines[] = "{$date}{$amount},50#{$date}900000{$i}#note/debt payment march/internal_reference/A462JE81";
    }

    return implode("\n", $lines);
}

it('successfully processing webhook request with 10000 transactions', function () {
    $client = Client::factory()->create();
    $webhookData = getWebhookData();

    // Measure memory usage and execution time
    $startMemory = memory_get_usage();
    $startTime = microtime(true);

    $response = call(
        'POST',
        apiRoute('webhooks/'.Bank::FOODICS->value),
        [],
        [],
        [],
        [
            'HTTP_X_CLIENT_ID' => $client->id,
            'HTTP_ACCEPT' => 'application/json',
        ],
        $webhookData,
    );

    $endTime = microtime(true);
    $endMemory = memory_get_usage();

    $executionTime = $endTime - $startTime; // in seconds
    $memoryUsage = ($endMemory - $startMemory) / 1024 / 1024; // in MB

    $response->assertStatus(200);

    $this->assertLessThan(1, $executionTime);
    $this->assertLessThan(20, $memoryUsage);
})->group('performance');

it('benchmarks 100 webhook calls with 1000 transactions each', function () {
    $client = Client::factory()->create();

    [$result, $executionTime] = Benchmark::value(function () use ($client) {
        for ($i = 0; $i < 100; $i++) {
            $webhookData = getWebhookData(1000);

            $response = call(
                'POST',
                apiRoute('webhooks/'.Bank::FOODICS->value),
                [],
                [],
                [],
                [
                    'HTTP_X_CLIENT_ID' => $client->id,
                    'HTTP_ACCEPT' => 'application/json',
                ],
                $webhookData,
            );

            $response->assertStatus(200);
        }

        return true;
    });

    $this->assertLessThan(3000, $executionTime, "Execution took too long: {$executionTime} ms");
})->group('performance');
