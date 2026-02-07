<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use App\Models\User;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'main_image',
        'images',
        'features',
        'content',
        'videos',
        'is_active',
        'display_order',
        'created_by',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'images' => 'array',
        'features' => 'array',
        'videos' => 'array',
        'content' => 'string',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /* =====================
       Relationships
    ===================== */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /* =====================
       Scopes (Important)
    ===================== */

    /**
     * Only active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order services by display_order then newest.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderByDesc('created_at');
    }

    /**
     * Scope Public 
     */
    public function scopePublic($query)
    {
        return $query
            ->where('is_active', true)
            ->orderBy('display_order');
    }


    /* =====================
       Helpers
    ===================== */

    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}
