<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'attachment_path',
        'ip_address',
        'read_at',
        'tag',
        'replied_at',
        'project_type',
        'services',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'services' => 'array',  // الكاست للعمود services ليتم تحويله إلى مصفوفة
    ];
}
