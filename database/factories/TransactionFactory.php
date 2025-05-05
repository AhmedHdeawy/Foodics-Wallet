<?php

namespace Database\Factories;

use App\Enums\Bank;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => 1,
            'reference' => $this->faker->unique()->word(),
            'amount' => $this->faker->randomFloat(2, 0, 10000),
            'transaction_date' => $this->faker->date(),
            'bank_name' => $this->faker->randomElement(Bank::values()),
            'meta' => [],
            'status' => $this->faker->randomElement(TransactionStatus::values()),
            'unique_identifier' => $this->faker->uuid(),
        ];
    }
}
