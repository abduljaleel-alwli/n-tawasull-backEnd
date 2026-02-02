<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Support\Auditable;

class CreateProduct
{
    use Auditable;

    /**
     * Create a new product.
     */
    public function execute(array $data): Product
    {
        Gate::authorize('create', Product::class);

        // Handle main image upload
        $mainImagePath = null;
        if (!empty($data['main_image'])) {
            $mainImagePath = $data['main_image']->store('products', 'public');
        }

        // Handle gallery images upload
        $images = [];
        if (!empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $images[] = $image->store('products/gallery', 'public');
            }
        }

        // 🔢 Calculate next display order
        $nextOrder = Product::max('display_order') ?? 0;
        $nextOrder++;

        $product = Product::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'main_image' => $mainImagePath,
            'images' => $images,
            'is_active' => $data['is_active'] ?? true,
            'display_order' => $nextOrder,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $this->audit('product.created', $product, [
            'title' => $product->title,
        ]);

        return $product;
    }
}
