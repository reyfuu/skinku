<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'IN';

    public const TYPE_OUT = 'OUT';

    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    public const TYPE_TRANSFER = 'TRANSFER';

    public const TYPE_PO_FULFILLMENT = 'PO_FULFILLMENT';

    public const TYPES = [
        self::TYPE_IN,
        self::TYPE_OUT,
        self::TYPE_ADJUSTMENT,
        self::TYPE_TRANSFER,
        self::TYPE_PO_FULFILLMENT,
    ];

    public $timestamps = false; // only created_at is tracked

    protected $fillable = [
        'product_id', 'user_id', 'movement_type', 'quantity',
        'before_qty', 'after_qty', 'reference_type', 'reference_id',
        'notes', 'created_by', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'before_qty' => 'integer',
            'after_qty' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
