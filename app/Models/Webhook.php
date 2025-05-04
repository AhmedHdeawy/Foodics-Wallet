<?php

namespace App\Models;

use App\Enums\Bank;
use App\Enums\WebhookStatus;
use Carbon\Carbon;
use Database\Factories\WebhookFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $client_id
 * @property string $raw_data
 * @property Bank $bank_name
 * @property WebhookStatus $status
 * @property string $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static WebhookFactory factory($count = null, $state = [])
 * @method static Builder pending()
 */
class Webhook extends Model
{
    /** @use HasFactory<WebhookFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'raw_data',
        'bank_name',
        'status',
        'error_message',
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
            'status' => WebhookStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => WebhookStatus::PROCESSING,
        ]);
    }

    public function markAsProcessed(): bool
    {
        return $this->update([
            'status' => WebhookStatus::PROCESSED,
        ]);
    }

    public function markAsFailed(?string $errorMessage = null): bool
    {
        return $this->update([
            'status' => WebhookStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    public function doNotProcess(): bool
    {
        return in_array($this->status, [
            WebhookStatus::PROCESSED,
            WebhookStatus::PROCESSING,
        ]);
    }

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::PENDING);
    }
}
