<?php

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CreateCategory
{
    /**
     * Create a new category.
     */
    public function execute(array $data): Category
    {
        Gate::authorize('create', Category::class);

        return Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);
    }
}
