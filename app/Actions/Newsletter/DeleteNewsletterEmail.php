<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterEmail;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteNewsletterEmail
{
    /**
     * تنفيذ عملية الحذف للبريد الإلكتروني.
     *
     * @param  NewsletterEmail  $email
     * @return void
     */
    public function execute(NewsletterEmail $email): void
    {
        // استرداد المستخدم الحالي باستخدام auth()
        $user = auth()->user();

        // التحقق من صلاحيات المستخدم باستخدام الـ Policy
        if ($user->cannot('delete', $email)) {
            throw new AuthorizationException('You do not have permission to delete this email.');
        }

        // حذف البريد الإلكتروني من قاعدة البيانات
        $email->delete();
    }
}
