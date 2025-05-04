<?php

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->client = Client::factory()->create(['balance' => 500]);
    $this->testDateTime = '2025-05-02 15:23:44+00:00';

    Carbon::setTestNow($this->testDateTime);
});

function transferRequest(
    int $clientId,
    float $amount = 50,
    bool $setNotes = true,
    string $paymentType = '5664',
    string $chargeDetails = 'RAS'
): TestResponse {
    return postJson(apiRoute('transfer'), [
        'client_id' => $clientId,
        'receiver_account_number' => 'SA6980000204608016211111',
        'receiver_bank_code' => 'FDCSSARI',
        'beneficiary_name' => 'Jane Doe',
        'amount' => $amount,
        'currency' => 'SAR',
        'reference' => '78472FDCSSARI8798',
        'payment_type' => $paymentType,
        'charge_details' => $chargeDetails,
        'notes' => $setNotes ? ['Payment for services', 'Invoice #12345'] : [],

    ]);
}

it('successfully returns the xml', function () {
    $response = transferRequest($this->client->id);

    $xml = simplexml_load_string($response->getContent());

    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');

    expect($xml->getName())->toBe('PaymentRequestMessage')
        ->toHaveAttribute('PaymentRequestMessage')
        ->and($xml->TransferInfo->Reference)
        ->toEqual('78472FDCSSARI8798')
        ->and($xml->TransferInfo->Date)
        ->toEqual($this->testDateTime)
        ->and($xml->TransferInfo->Amount)
        ->toEqual("50.00")
        ->and($xml->TransferInfo->Currency)
        ->toEqual('SAR')
        // Later we will get the account number from the client
        // ->and($xml->SenderInfo->AccountNumber)
        // ->toEqual($this->client->account_number)
        ->and($xml->ReceiverInfo->BankCode)
        ->toEqual('FDCSSARI')
        ->and($xml->ReceiverInfo->AccountNumber)
        ->toEqual('SA6980000204608016211111')
        ->and($xml->ReceiverInfo->BeneficiaryName)
        ->toEqual('Jane Doe')
        ->and($xml->Notes->Note[0])
        ->toEqual('Payment for services')
        ->and($xml->Notes->Note[1])
        ->toEqual('Invoice #12345')
        ->and($xml->PaymentType)
        ->toEqual('5664')
        ->and($xml->ChargeDetails)
        ->toEqual('RAS');
});

it('tests insufficient balance', function () {
    $response = transferRequest($this->client->id, 5876389704.00);

    $response
        ->assertStatus(400)
        ->assertJson([
            'error' => 'Insufficient balance'
        ]);
});

it('does not have Nodes tag if no nodes present', function () {
    $response = transferRequest($this->client->id, 50, false);
    $xml = simplexml_load_string($response->getContent());

    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');

    expect($xml->getName())->toBe('PaymentRequestMessage')
        ->toHaveAttribute('PaymentRequestMessage')
        ->and($xml->TransferInfo->Reference)
        ->toEqual('78472FDCSSARI8798')
        ->and($xml->Notes)
        ->toBeEmpty();
});

it('does not have payment type tag if its value 99', function () {
    $response = transferRequest($this->client->id, 50, false, '99');
    $xml = simplexml_load_string($response->getContent());

    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');

    expect($xml->getName())->toBe('PaymentRequestMessage')
        ->toHaveAttribute('PaymentRequestMessage')
        ->and($xml->TransferInfo->Reference)
        ->toEqual('78472FDCSSARI8798')
        ->and($xml->PaymentType)
        ->toBeEmpty();
});

it('does not have charge details tag if its value SHA', function () {
    $response = transferRequest($this->client->id, 50, false, '100', 'SHA');
    $xml = simplexml_load_string($response->getContent());

    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');

    expect($xml->getName())->toBe('PaymentRequestMessage')
        ->toHaveAttribute('PaymentRequestMessage')
        ->and($xml->TransferInfo->Reference)
        ->toEqual('78472FDCSSARI8798')
        ->and($xml->ChargeDetails)
        ->toBeEmpty();
});
