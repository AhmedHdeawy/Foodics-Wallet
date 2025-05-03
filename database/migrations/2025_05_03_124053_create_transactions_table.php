<?php

use App\Enums\Bank;
use App\Enums\TransactionStatus;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('reference')->index();
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->enum('bank_name', Bank::values());
            $table->json('meta')->nullable();

            /**
             * Another option to ensure uniqueness could be to create a unique identifier
             * for each transaction, but this would require additional logic to generate
             * and store the unique identifier.
             * see \App\Models\Transaction::generateUniqueIdentifier()
             */
            // $table->string('unique_identifier')->unique();


            $table->enum('status', TransactionStatus::values())->default(TransactionStatus::PENDING);
            $table->timestamps();
            $table->softDeletes();

            // Create a unique index to prevent duplicate transactions
            $table->unique(['client_id', 'reference', 'transaction_date', 'bank_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
