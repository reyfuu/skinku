<?php

namespace App\Models\Concerns;

use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Gives a model a polymorphic gallery of files grouped by "collection".
 */
trait HasFiles
{
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /** Files in a collection, ordered. */
    public function filesIn(string $collection)
    {
        return $this->files()->where('collection', $collection)->orderBy('sort_order')->orderBy('id');
    }

    public function firstFileUrl(string $collection): ?string
    {
        $file = $this->filesIn($collection)->first();

        return $file?->url();
    }

    /** @return array<int,string> */
    public function fileUrls(string $collection): array
    {
        return $this->filesIn($collection)->get()->map(fn (File $f) => $f->url())->all();
    }

    /** @return array<int,array{id:int,url:string}> */
    public function fileGallery(string $collection): array
    {
        return $this->filesIn($collection)->get()->map(fn (File $f) => ['id' => $f->id, 'url' => $f->url()])->all();
    }
}
