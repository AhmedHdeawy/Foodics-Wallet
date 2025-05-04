<?php

use App\Services\TransferXmlBuilder\Concretes\TransferXmlBuilder;
use Carbon\Carbon;

beforeEach(function () {
    $this->testDateTime = '2025-05-02 15:23:44+00:00';
    Carbon::setTestNow($this->testDateTime);

    $this->xmlBuilder = new TransferXmlBuilder;
    $this->reference = uniqid();
    $this->date = Carbon::now();
    $this->amount = '156.50';
    $this->currency = 'SAR';
    $this->senderAccountNumber = (string) random_int(23432434, 54395385743853);
    $this->receiverBankCode = 'FDCSSARI';
    $this->receiverAccountNumber = '1234567890123456';
    $this->beneficiaryName = 'John Doe';
    $this->notes = ['Payment for services', 'internal_reference-A462JE81'];
    $this->paymentType = '01';
    $this->chargeDetails = 'SHA';
});

it('build basic xml', function () {
    $xmlBuilder = $this->xmlBuilder
        ->setReference($this->reference)
        ->setDate($this->date)
        ->setAmount($this->amount)
        ->setCurrency($this->currency)
        ->setSenderAccountNumber($this->senderAccountNumber)
        ->setReceiverBankCode($this->receiverBankCode)
        ->setReceiverAccountNumber($this->receiverAccountNumber)
        ->setBeneficiaryName($this->beneficiaryName)
        ->setNotes($this->notes)
        ->setPaymentType($this->paymentType)
        ->setChargeDetails($this->chargeDetails)
        ->build();

    expect($xmlBuilder)->toContain('<?xml version="1.0" encoding="utf-8"?>')
        ->and($xmlBuilder)->toContain('<PaymentRequestMessage>')
        ->and($xmlBuilder)->toContain('<TransferInfo>')
        ->and($xmlBuilder)->toContain("<Reference>$this->reference</Reference>")
        ->and($xmlBuilder)->toContain("<Date>$this->testDateTime</Date>")
        ->and($xmlBuilder)->toContain("<Amount>$this->amount</Amount>")
        ->and($xmlBuilder)->toContain("<Currency>$this->currency</Currency>")
        ->and($xmlBuilder)->toContain('<SenderInfo>')
        ->and($xmlBuilder)->toContain("<AccountNumber>$this->senderAccountNumber</AccountNumber>")
        ->and($xmlBuilder)->toContain('<ReceiverInfo>')
        ->and($xmlBuilder)->toContain("<BankCode>$this->receiverBankCode</BankCode>")
        ->and($xmlBuilder)->toContain("<AccountNumber>$this->receiverAccountNumber</AccountNumber>")
        ->and($xmlBuilder)->toContain("<BeneficiaryName>$this->beneficiaryName</BeneficiaryName>")
        ->and($xmlBuilder)->toContain('<Notes>')
        ->and($xmlBuilder)->toContain("<Note>{$this->notes[0]}</Note>")
        ->and($xmlBuilder)->toContain("<Note>{$this->notes[1]}</Note>")
        ->and($xmlBuilder)->toContain("<PaymentType>$this->paymentType</PaymentType>")
        ->and($xmlBuilder)->toContain("<ChargeDetails>$this->chargeDetails</ChargeDetails>");
});

it('validates the required fields before build', function () {
    $xmlBuilder = $this->xmlBuilder
        ->setReference($this->reference)
        ->build();
})->throws(InvalidArgumentException::class);

it('build xml without optional fields', function () {
    $xmlBuilder = $this->xmlBuilder
        ->setReference($this->reference)
        ->setDate($this->date)
        ->setAmount($this->amount)
        ->setCurrency($this->currency)
        ->setSenderAccountNumber($this->senderAccountNumber)
        ->setReceiverBankCode($this->receiverBankCode)
        ->setReceiverAccountNumber($this->receiverAccountNumber)
        ->setBeneficiaryName($this->beneficiaryName)
        ->build();

    expect($xmlBuilder)->toContain('<?xml version="1.0" encoding="utf-8"?>')
        ->and($xmlBuilder)->toContain('<PaymentRequestMessage>')
        ->and($xmlBuilder)->toContain('<TransferInfo>')
        ->and($xmlBuilder)->toContain("<Reference>$this->reference</Reference>")
        ->and($xmlBuilder)->toContain("<Date>$this->testDateTime</Date>")
        ->and($xmlBuilder)->toContain("<Amount>$this->amount</Amount>")
        ->and($xmlBuilder)->toContain("<Currency>$this->currency</Currency>")
        ->and($xmlBuilder)->toContain('<SenderInfo>')
        ->and($xmlBuilder)->toContain("<AccountNumber>$this->senderAccountNumber</AccountNumber>")
        ->and($xmlBuilder)->toContain('<ReceiverInfo>')
        ->and($xmlBuilder)->toContain("<BankCode>$this->receiverBankCode</BankCode>")
        ->and($xmlBuilder)->toContain("<AccountNumber>$this->receiverAccountNumber</AccountNumber>")
        ->and($xmlBuilder)->toContain("<BeneficiaryName>$this->beneficiaryName</BeneficiaryName>");
});
