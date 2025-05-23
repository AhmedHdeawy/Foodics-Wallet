<?php

namespace App\Services\Transfers\Concretes;

use App\Exceptions\InsufficientBalance;
use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transfers\Contracts\TransferServiceContract;
use App\Services\TransferXmlBuilder\Contracts\TransferXmlBuilderContract;
use Exception;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

class TransferService implements TransferServiceContract
{
    private const array NOT_ALLOWED_PAYMENT_TYPES = ['99'];

    private const array NOT_ALLOWED_CHARGE_DETAILS = ['SHA'];

    public function __construct(
        protected TransferXmlBuilderContract $xmlBuilder,
        protected ClientServiceContract $clientService
    ) {}

    /**
     * @throws InsufficientBalance
     * @throws Exception
     */
    public function transferMoney(array $data): string
    {
        $lock = $this->handleRaceCondition($data);

        try {
            $this->checkIfTransferIsValid($data);

            $xml = $this->generateXml($data);
        } finally {
            $lock->release();
        }

        return $xml;
    }

    /**
     * @throws Exception
     */
    private function handleRaceCondition(array $data): Lock
    {
        $lockKey = 'transfer_lock_'.$data['client_id'].'_'.$data['receiver_account_number'];
        $lock = Cache::lock($lockKey, 2);
        if (! $lock->get()) {
            throw new Exception('Another transaction with the same account is in progress, please try again later.');
        }

        return $lock;
    }

    /**
     * @throws InsufficientBalance
     */
    private function checkIfTransferIsValid(array $data): void
    {
        $client = $this->clientService->validateClient($data['client_id'], ['id', 'balance']);

        // Check if client has sufficient balance
        if ($client->balance < $data['amount']) {
            throw new InsufficientBalance;
        }
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
            ->setSenderAccountNumber((string) random_int(23432434, 54395385743853))
            ->setReceiverBankCode($data['receiver_bank_code'])
            ->setReceiverAccountNumber($data['receiver_account_number'])
            ->setBeneficiaryName($data['beneficiary_name']);

        /**
         * The task mentioned that The Notes tag must not be present if there are notes.
         * But I guess you means that if there are no notes, the tag should not be present.
         */
        if (! empty($data['notes'])) {
            foreach ($data['notes'] as $note) {
                $xmlBuilder->addNote($note);
            }
        }

        if (isset($data['payment_type']) && ! in_array($data['payment_type'], self::NOT_ALLOWED_PAYMENT_TYPES)) {
            $xmlBuilder->setPaymentType($data['payment_type']);
        }

        if (isset($data['charge_details']) && ! in_array($data['charge_details'], self::NOT_ALLOWED_CHARGE_DETAILS)) {
            $xmlBuilder->setChargeDetails($data['charge_details']);
        }

        return $xmlBuilder->build();
    }
}
