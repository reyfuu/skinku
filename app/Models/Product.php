<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'name', 'sku', 'category', 'description', 'image', 'image_path', 'images',
        'price_distributor', 'price_reseller', 'price_retail', 'cogs',
        'hq_stock', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price_distributor' => 'decimal:2',
            'price_reseller' => 'decimal:2',
            'price_retail' => 'decimal:2',
            'cogs' => 'decimal:2',
            'hq_stock' => 'integer',
            'images' => 'array',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** Primary product image URL (first gallery image, or legacy single path). */
    public function imageUrl(): ?string
    {
        $first = $this->images[0] ?? $this->image_path;

        return $first ? Storage::disk('public')->url($first) : null;
    }

    /** All gallery image URLs (resized). */
    public function imageUrls(): array
    {
        $paths = ! empty($this->images) ? $this->images : array_filter([$this->image_path]);

        return array_map(fn ($p) => Storage::disk('public')->url($p), $paths);
    }

    /** Returns the unit price for a given role. */
    public function priceForRole(string $role): float
    {
        return match ($role) {
            User::ROLE_DISTRIBUTOR => (float) $this->price_distributor,
            User::ROLE_RESELLER => (float) $this->price_reseller,
            default => (float) $this->price_retail,
        };
    }
}
