<?php

use App\Enums\Bank;
use App\Enums\WebhookStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->text('raw_data');
            $table->enum('bank_name', Bank::values());
            $table->enum('status', WebhookStatus::values())->default(WebhookStatus::PENDING);
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Index for querying unprocessed webhooks efficiently
            $table->index(['status']);
            $table->index(['client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
