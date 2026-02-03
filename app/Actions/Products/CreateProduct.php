<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
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

        return DB::transaction(function () use ($data) {

            /* =====================
               Images Handling
            ===================== */

            $mainImagePath = null;
            if (!empty($data['main_image'])) {
                $mainImagePath = $data['main_image']->store('products', 'public');
            }

            $images = [];
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    $images[] = $image->store('products/gallery', 'public');
                }
            }

            /* =====================
               Display Order
            ===================== */

            $nextOrder = (Product::max('display_order') ?? 0) + 1;

            /* =====================
               Create Product
            ===================== */

            $product = Product::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'main_image' => $mainImagePath,
                'images' => $images,
                'is_active' => $data['is_active'] ?? true,
                'display_order' => $nextOrder,
                'created_by' => Auth::id(),
            ]);

            /* =====================
               Sync Tags (NEW)
            ===================== */

            if (!empty($data['tags']) && is_array($data['tags'])) {
                $product->syncTags($data['tags']);
            }

            /* =====================
               Audit
            ===================== */

            $this->audit('product.created', $product, [
                'title' => $product->title,
                'tags'  => $product->tags->pluck('name')->toArray(),
            ]);

            return $product;
        });
    }
}
