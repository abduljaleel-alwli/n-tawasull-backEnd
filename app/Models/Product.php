<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use App\Models\User;

class Product extends Model
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
        'is_active',
        'display_order',
        'created_by',
        'meta_title',
        'meta_description',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
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
     * Only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order products by display_order then newest.
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
