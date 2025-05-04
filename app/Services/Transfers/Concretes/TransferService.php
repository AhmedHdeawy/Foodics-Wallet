<?php

namespace App\Services\Transfers\Concretes;

use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transfers\Contracts\TransferServiceContract;
use App\Services\TransferXmlBuilder\Contracts\TransferXmlBuilderContract;
use InvalidArgumentException;
use Random\RandomException;

class TransferService implements TransferServiceContract
{
    private const array NOT_ALLOWED_PAYMENT_TYPES = ['99'];
    private const array NOT_ALLOWED_CHARGE_DETAILS = ['SHA'];

    public function __construct(
        protected TransferXmlBuilderContract $xmlBuilder,
        protected ClientServiceContract $clientService
    ) {
    }

    /**
     * @param  array  $data
     * @return string
     * @throws RandomException
     */
    public function transferMoney(array $data): string
    {
        $client = $this->clientService->validateClient($data['client_id'], ['id', 'balance']);

        // Check if client has sufficient balance
        if ($client->balance < $data['amount']) {
            throw new InvalidArgumentException('Insufficient balance');
        }

        return $this->generateXml($data);
    }

    /**
     * @throws RandomException
     */
    private function generateXml(array $data): string
    {
        $xmlBuilder = $this->xmlBuilder
            ->setReference($data['reference'])
            ->setDate(now())
            ->setAmount($data['amount'])
            ->setCurrency($data['currency'] ?? 'SAR')
            // Later we will get the account number from the client
            ->setSenderAccountNumber(random_int(23432434, 54395385743853))
            ->setReceiverBankCode($data['receiver_bank_code'])
            ->setReceiverAccountNumber($data['receiver_account_number'])
            ->setBeneficiaryName($data['beneficiary_name']);

        /**
         * The task mentioned that The Notes tag must not be present if there are notes.
         * But I guess you means that if there are no notes, the tag should not be present.
         */
        if (!empty($data['notes'])) {
            foreach ($data['notes'] as $note) {
                $xmlBuilder->addNote($note);
            }
        }

        if (!in_array($data['payment_type'], self::NOT_ALLOWED_PAYMENT_TYPES)) {
            $xmlBuilder->setPaymentType($data['payment_type']);
        }

        if (!in_array($data['charge_details'], self::NOT_ALLOWED_CHARGE_DETAILS)) {
            $xmlBuilder->setChargeDetails($data['charge_details']);
        }

        return $xmlBuilder->build();
    }
}
