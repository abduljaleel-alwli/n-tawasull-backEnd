<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterEmail;
use Illuminate\Auth\Access\AuthorizationException;

class ChangeEmailStatus
{
    /**
     * تنفيذ تغيير حالة البريد الإلكتروني (مفعل / غير مفعل).
     *
     * @param  NewsletterEmail  $email
     * @return void
     */
    public function execute(NewsletterEmail $email): void
    {
        // استرداد المستخدم الحالي
        $user = auth()->user();

        // التحقق من صلاحيات المستخدم باستخدام الـ Policy
        if ($user->cannot('toggleSubscriptionStatus', $email)) {
            throw new AuthorizationException('You do not have permission to change the status of this email.');
        }

        // تغيير حالة الاشتراك (تفعيل / تعطيل)
        $email->is_subscribed = !$email->is_subscribed; // عكس الحالة
        $email->save();
    }
}
