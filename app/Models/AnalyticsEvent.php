<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'event',
        'entity_type',
        'entity_id',
        'page',
        'source',
        'ip',
        'user_agent',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
