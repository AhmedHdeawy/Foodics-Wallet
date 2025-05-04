<?php

namespace App\Services\TransferXmlBuilder\Concretes;

use App\Services\TransferXmlBuilder\Contracts\TransferXmlBuilderContract;
use Carbon\Carbon;
use DOMDocument;
use InvalidArgumentException;
use SimpleXMLElement;

class TransferXmlBuilder implements TransferXmlBuilderContract
{
    private string $reference;
    private Carbon $date;
    private float $amount;
    private string $currency = 'SAR';
    private string $senderAccountNumber;
    private string $receiverBankCode;
    private string $receiverAccountNumber;
    private string $beneficiaryName;
    private array $notes = [];
    private string $paymentType;
    private string $chargeDetails;

    /**
     * Set the reference
     *
     * @param  string  $reference
     * @return self
     */
    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Set the date
     *
     * @param  Carbon  $date
     * @return self
     */
    public function setDate(Carbon $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Set the amount
     *
     * @param  float  $amount
     * @return self
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Set the currency
     *
     * @param  string  $currency
     * @return self
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set the sender account number
     *
     * @param  string  $accountNumber
     * @return self
     */
    public function setSenderAccountNumber(string $accountNumber): self
    {
        $this->senderAccountNumber = $accountNumber;
        return $this;
    }

    /**
     * Set the receiver bank code
     *
     * @param  string  $bankCode
     * @return self
     */
    public function setReceiverBankCode(string $bankCode): self
    {
        $this->receiverBankCode = $bankCode;
        return $this;
    }

    /**
     * Set the receiver account number
     *
     * @param  string  $accountNumber
     * @return self
     */
    public function setReceiverAccountNumber(string $accountNumber): self
    {
        $this->receiverAccountNumber = $accountNumber;
        return $this;
    }

    /**
     * Set the beneficiary name
     *
     * @param  string  $name
     * @return self
     */
    public function setBeneficiaryName(string $name): self
    {
        $this->beneficiaryName = $name;
        return $this;
    }

    /**
     * Add a note
     *
     * @param  string  $note
     * @return self
     */
    public function addNote(string $note): self
    {
        $this->notes[] = $note;
        return $this;
    }

    /**
     * Set all notes
     *
     * @param  array  $notes
     * @return self
     */
    public function setNotes(array $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Set the payment type
     *
     * @param  string  $paymentType
     * @return self
     */
    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    /**
     * Set the charge details
     *
     * @param  string  $chargeDetails
     * @return self
     */
    public function setChargeDetails(string $chargeDetails): self
    {
        $this->chargeDetails = $chargeDetails;
        return $this;
    }

    /**
     * Validate that all required fields are set
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        $requiredFields = [
            'reference' => $this->reference ?? null,
            'date' => $this->date ?? null,
            'amount' => $this->amount ?? null,
            'currency' => $this->currency ?? null,
            'senderAccountNumber' => $this->senderAccountNumber ?? null,
            'receiverBankCode' => $this->receiverBankCode ?? null,
            'receiverAccountNumber' => $this->receiverAccountNumber ?? null,
            'beneficiaryName' => $this->beneficiaryName ?? null,
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $value) {
            if ($value === null) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new InvalidArgumentException(
                'The following required fields are missing: '.implode(', ', $missingFields)
            );
        }
    }

    /**
     * Build the XML
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function build(): string
    {
        $this->validate();

        // Create the root XML element
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><PaymentRequestMessage></PaymentRequestMessage>');

        // Add TransferInfo section
        $transferInfo = $xml->addChild('TransferInfo');
        $transferInfo->addChild('Reference', $this->reference);
        $transferInfo->addChild('Date', $this->date->format('Y-m-d H:i:sP'));
        $transferInfo->addChild('Amount', number_format($this->amount, 2, '.', ''));
        $transferInfo->addChild('Currency', $this->currency);

        // Add SenderInfo section
        $senderInfo = $xml->addChild('SenderInfo');
        $senderInfo->addChild('AccountNumber', $this->senderAccountNumber);

        // Add ReceiverInfo section
        $receiverInfo = $xml->addChild('ReceiverInfo');
        $receiverInfo->addChild('BankCode', $this->receiverBankCode);
        $receiverInfo->addChild('AccountNumber', $this->receiverAccountNumber);
        $receiverInfo->addChild('BeneficiaryName', $this->beneficiaryName);

        if (!empty($this->notes)) {
            $notesElement = $xml->addChild('Notes');
            foreach ($this->notes as $note) {
                $notesElement->addChild('Note', $note);
            }
        }

        if (!empty($this->paymentType)) {
            $xml->addChild('PaymentType', $this->paymentType);
        }

        if (!empty($this->chargeDetails)) {
            $xml->addChild('ChargeDetails', $this->chargeDetails);
        }

        // Build the XML string
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

}
