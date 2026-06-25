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
        'name', 'sku', 'category', 'description', 'image', 'image_path',
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
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** Public URL for the product image, or null when none was uploaded. */
    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
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
