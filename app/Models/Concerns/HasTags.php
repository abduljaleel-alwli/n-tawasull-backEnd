<?php

namespace App\Models\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags   
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function syncTags(array $tags): void
    {
        $tagIds = collect($tags)->map(function ($tag) {
            return Tag::firstOrCreate(
                ['slug' => str($tag)->slug()],
                ['name' => $tag]
            )->id;
        });

        $this->tags()->sync($tagIds);
    }
}