<?php

namespace App\Http\Controllers;

use App\Models\NewsletterEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * الاشتراك في النشرة البريدية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function subscribe(Request $request)
    {
        try {
            // التحقق من صحة البريد الإلكتروني
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:newsletter_emails,email', // تحقق من صحة البريد
            ]);

            // إذا كانت البيانات غير صالحة
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'أوه! يبدو أن هذا البريد الإلكتروني قد تم الاشتراك به بالفعل أو غير صالح.',
                    'errors' => $validator->errors(),
                ], 422); // 422 يعني Unprocessable Entity
            }

            // حفظ البريد الإلكتروني في الجدول
            NewsletterEmail::create([
                'email' => $request->email,
                'subscribed_at' => now(),  // تعيين الوقت الحالي لحفظ تاريخ الاشتراك
            ]);

            // رد بنجاح
            return response()->json([
                'message' => 'تم الاشتراك بنجاح! شكراً لانضمامك إلينا. سنوافيك بأحدث الأخبار قريبًا.',
            ], 200);

        } catch (\Exception $e) {
            // تسجيل الخطأ في الـ log
            Log::error('Error subscribing to newsletter', [
                'exception' => $e->getMessage(),
                'email' => $request->email
            ]);

            // إرجاع رد خطأ عام مع رسالة
            return response()->json([
                'message' => 'حدث خطأ غير متوقع أثناء الاشتراك. لكن لا داعي للقلق، سنقوم بحل المشكلة قريباً.',
            ], 500); // 500 يعني Internal Server Error
        }
    }
}
