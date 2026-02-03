<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DeleteProduct
{
    use Auditable;

    public function execute(Product $product): void
    {
        Gate::authorize('delete', $product);

        DB::transaction(function () use ($product) {

            /* =====================
               Detach Tags (IMPORTANT)
            ===================== */

            $product->tags()->detach();

            /* =====================
               Delete Images
            ===================== */

            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }

            if (is_array($product->images)) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            /* =====================
               Soft Delete Product
            ===================== */

            $product->forceDelete();

            /* =====================
               Audit
            ===================== */

            $this->audit(
                'product.deleted',
                $product,
                [
                    'title' => $product->title,
                    'tags'  => $product->tags->pluck('name')->toArray(),
                ]
            );
        });
    }
}
