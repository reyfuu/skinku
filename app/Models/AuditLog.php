<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false; // only created_at is tracked

    protected $fillable = [
        'action', 'target_type', 'target_id', 'target_user_id', 'target_email',
        'performed_by', 'performed_by_email', 'before_data', 'after_data',
        'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
