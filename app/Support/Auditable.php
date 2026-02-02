<?php

namespace App\Support;

use App\Models\AuditLog;

trait Auditable
{
    public function audit(string $action, $target, array $metadata = []): void
    {
        AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => $action,
            'target_type' => get_class($target),
            'target_id'   => $target->id,
            'metadata'    => $metadata,
        ]);
    }
}
