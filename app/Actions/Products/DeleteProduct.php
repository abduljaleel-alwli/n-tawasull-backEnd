<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DeleteProduct
{
    use Auditable;

    public function execute(Product $product): void
    {
        Gate::authorize('delete', $product);

        // 🔴 Delete main image
        if ($product->main_image) {
            Storage::disk('public')->delete($product->main_image);
        }

        // 🔴 Delete gallery images
        if (is_array($product->images)) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        $this->audit(
            'product.deleted',
            $product,
            [
                'title' => $product->title,
            ]
        );

    }
}
