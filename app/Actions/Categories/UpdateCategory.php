<?php

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UpdateCategory
{
    /**
     * Update an existing category.
     */
    public function execute(Category $category, array $data): Category
    {
        Gate::authorize('update', $category);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return $category;
    }
}
