<?php

namespace App\Models;

use App\Enums\Bank;
use App\Enums\TransactionStatus;
use Carbon\Carbon;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $client_id
 * @property string $reference
 * @property string $unique_identifier
 * @property float $amount
 * @property Carbon $transaction_date
 * @property array $meta
 * @property Bank $bank_name
 * @property TransactionStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static TransactionFactory factory($count = null, $state = [])
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'reference',
        'amount',
        'transaction_date',
        'bank_name',
        'meta',
        'status',
        'unique_identifier',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bank_name' => Bank::class,
            'status' => TransactionStatus::class,
            'meta' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Generate a unique identifier for this transaction
     * This helps prevent duplicate transactions
     * Combines bank name, reference and transaction date
     */
    public static function generateUniqueIdentifier(
        string $clientId,
        string $bankName,
        string $reference,
        string $date
    ): string {
        return md5($clientId.$bankName.$reference.$date);
    }
}
