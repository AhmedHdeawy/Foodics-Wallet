<?php

namespace App\Services\Webhooks\Contracts;

use App\Models\Webhook;

interface WebhookServiceContract
{
    public function store(array $data): Webhook;

}
