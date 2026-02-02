<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Create product.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Update product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Delete product (soft delete).
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Toggle product active/inactive.
     */
    public function toggleActive(User $user, Product $product): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Reorder products.
     */
    public function reorder(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
