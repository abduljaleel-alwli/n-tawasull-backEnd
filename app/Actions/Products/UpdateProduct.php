<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Support\Auditable;

class UpdateProduct
{
    use Auditable;

    public function execute(Product $product, array $data): Product
    {
        Gate::authorize('update', $product);

        return DB::transaction(function () use ($product, $data) {
            /* =====================
               Main Image
            ===================== */
            // 🔴 Main image replacement
            if (array_key_exists('main_image', $data) && $data['main_image']) {

                // Delete old main image
                if ($product->main_image) {
                    Storage::disk('public')->delete($product->main_image);
                }

                // Store new main image
                $product->main_image = $data['main_image']->store('products', 'public');
            }

            /* =====================
               Gallery Images
            ===================== */
            // 🔴 Gallery replacement (only if new images sent)
            if (
                array_key_exists('images', $data)
                && is_array($data['images'])
                && count($data['images']) > 0
            ) {

                // Delete old gallery images
                if (is_array($product->images)) {
                    foreach ($product->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                // Store new gallery images
                $paths = [];
                foreach ($data['images'] as $image) {
                    $paths[] = $image->store('products/gallery', 'public');
                }

                $product->images = $paths;
            }

            /* =====================
               Update Fields
            ===================== */
            // 🔵 Update basic fields
            $product->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'is_active' => $data['is_active'] ?? $product->is_active,
                'display_order' => $data['display_order'] ?? $product->display_order,
                'tags' => !empty($data['tags']) ? json_encode($data['tags']) : null, // إضافة الـ tags هنا
            ]);

            /* =====================
            Update Tags
            ===================== */
            // if (array_key_exists('tags', $data) && !empty($data['tags'])) {
            //     $tags = is_string($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : $data['tags'];
            //     $product->tags = !empty($tags) ? json_encode($tags) : null;
            // }


            /* =====================
               Audit
            ===================== */

            $this->audit('product.updated', $product, [
                'updated_fields' => array_keys($data),
            ]);

            return $product->refresh();
        });
    }
}
