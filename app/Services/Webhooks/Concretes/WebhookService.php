<?php

namespace App\Services\Webhooks\Concretes;

use App\Models\Webhook;
use App\Services\Webhooks\Contracts\WebhookServiceContract;

class WebhookService implements WebhookServiceContract
{

    /**
     * @param  array  $data
     * @return Webhook
     */
    public function store(array $data): Webhook
    {
        return Webhook::query()->create($data);
    }
}
