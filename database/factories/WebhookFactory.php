<?php

namespace Database\Factories;

use App\Enums\Bank;
use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'raw_data' => $this->faker->text(),
            'bank_name' => $this->faker->randomElement(Bank::values()),
            'status' => $this->faker->randomElement(WebhookStatus::values()),
            'error_message' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
