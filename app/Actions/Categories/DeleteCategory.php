<?php

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeleteCategory
{
    /**
     * Delete a category.
     */
    public function execute(Category $category): void
    {
        Gate::authorize('delete', $category);

        if ($category->services()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('Cannot delete a category that has services'),
            ]);
        }

        if ($category->projects()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('Cannot delete a category that has projects'),
            ]);
        }

        $category->delete();
    }
}
