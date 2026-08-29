<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'barcode',
        'image',
        'category_id',
        'stock',
        'min_stock',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            if (! $product->forceDeleting && $product->barcode !== null) {
                $product->barcode = null;
                $product->saveQuietly();
            }
        });

        static::restoring(function (Product $product) {
            $exists = static::query()
                ->where('barcode', $product->barcode)
                ->where('id', '!=', $product->id)
                ->exists();

            if ($product->barcode === null || $exists) {
                $product->barcode = static::generateUniqueBarcode();
            }
        });
    }

    public static function generateUniqueBarcode(): string
    {
        do {
            $code = str_pad((string) mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
            $barcode = $code.static::ean13CheckDigit($code);
        } while (static::query()->where('barcode', $barcode)->exists());

        return $barcode;
    }

    protected static function ean13CheckDigit(string $code): string
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $code[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $remainder = $sum % 10;

        return $remainder === 0 ? '0' : (string) (10 - $remainder);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function stockStatus(): string
    {
        if ($this->stock <= 0) {
            return 'critical';
        }

        if ($this->min_stock > 0 && $this->stock <= $this->min_stock) {
            return 'low';
        }

        if ($this->min_stock > 0 && $this->stock <= ceil($this->min_stock * 1.25)) {
            return 'warning';
        }

        return 'ok';
    }
}
