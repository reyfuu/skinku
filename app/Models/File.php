<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Central store for every uploaded file/image. Linked polymorphically to the
 * owning record (product, purchase order, …) via fileable_type + fileable_id.
 */
class File extends Model
{
    protected $fillable = [
        'fileable_type', 'fileable_id', 'collection', 'disk', 'path',
        'original_name', 'mime_type', 'size', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Remove the physical file when its DB row is deleted.
        static::deleting(function (File $file) {
            Storage::disk($file->disk ?: 'public')->delete($file->path);
        });
    }

    public function fileable()
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
