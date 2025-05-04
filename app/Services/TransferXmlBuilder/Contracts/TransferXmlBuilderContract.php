<?php

namespace App\Services\TransferXmlBuilder\Contracts;

use Carbon\Carbon;

interface TransferXmlBuilderContract
{
    public function setReference(string $reference): self;

    public function setDate(Carbon $date): self;

    public function setAmount(float $amount): self;

    public function setCurrency(string $currency): self;

    public function setSenderAccountNumber(string $accountNumber): self;

    public function setReceiverBankCode(string $bankCode): self;

    public function setReceiverAccountNumber(string $accountNumber): self;

    public function setBeneficiaryName(string $name): self;

    public function addNote(string $note): self;

    public function setNotes(array $notes): self;

    public function setPaymentType(string $paymentType): self;

    public function setChargeDetails(string $chargeDetails): self;

    public function build(): string;
}
