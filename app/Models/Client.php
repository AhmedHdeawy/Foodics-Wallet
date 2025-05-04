<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @property string $name
 * @property float $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static ClientFactory factory($count = null, $state = [])
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'balance',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
