<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;

class ToggleProductStatus
{
    use Auditable;

    public function execute(Product $product): Product
    {
        Gate::authorize('update', $product);
        
        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        $this->audit('product.toggled', $product, [
            'is_active' => $product->is_active,
        ]);

        return $product;
    }
}
