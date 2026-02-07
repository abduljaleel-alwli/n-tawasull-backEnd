<?php

namespace App\Policies;

use App\Models\NewsletterEmail;
use App\Models\User;

class NewsletterPolicy
{
    /**
     * إنشاء بريد إلكتروني جديد للنشرة.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * تحديث بيانات البريد الإلكتروني في النشرة.
     */
    public function update(User $user, NewsletterEmail $newsletterEmail): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * حذف البريد الإلكتروني من النشرة (الحذف الناعم).
     */
    public function delete(User $user, NewsletterEmail $newsletterEmail): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * تفعيل/تعطيل الاشتراك في النشرة.
     */
    public function toggleSubscriptionStatus(User $user, NewsletterEmail $newsletterEmail): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
