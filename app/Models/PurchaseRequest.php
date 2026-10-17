<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_DELIVERED = 'delivered';

    protected $fillable = [
        'user_id',
        'status',
        'note',
        'delivered_at',
        'delivered_by',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PARTIAL], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DELIVERED => 'Recebida',
            self::STATUS_PARTIAL => 'Parcial',
            default => 'Pendente',
        };
    }
}
