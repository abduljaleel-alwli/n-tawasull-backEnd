<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Support\Auditable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReorderProducts
{
    use Auditable;

    /**
     * @param array<int, int> $ids
     */
    public function execute(array $ids): void
    {
        Gate::authorize('access-dashboard');

        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Product::where('id', $id)->update([
                    'display_order' => $index + 1,
                ]);
            }
        });
    }
}
