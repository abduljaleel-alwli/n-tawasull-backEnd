<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Support\Auditable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReorderProjects
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
                Project::where('id', $id)->update([
                    'display_order' => $index + 1,
                ]);
            }
        });
    }
}
