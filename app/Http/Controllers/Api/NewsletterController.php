<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class NewsletterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/subscribe",
     *     summary="Subscribe to the newsletter",
     *     tags={"Newsletter"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="تم الاشتراك بنجاح! شكراً لانضمامك إلينا. سنوافيك بأحدث الأخبار قريبًا.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed or email already subscribed",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="message", type="string", example="أوه! يبدو أن هذا البريد الإلكتروني قد تم الاشتراك به بالفعل أو غير صالح.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="حدث خطأ غير متوقع أثناء الاشتراك. لكن لا داعي للقلق، سنقوم بحل المشكلة قريباً.")
     *         )
     *     )
     * )
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
                    'errors' => $validator->errors(),
                    'message' => 'أوه! يبدو أن هذا البريد الإلكتروني قد تم الاشتراك به بالفعل أو غير صالح.',
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

    /**
     * @OA\Schema(
     *     schema="NewsletterEmail",
     *     type="object",
     *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *     @OA\Property(property="subscribed_at", type="string", format="date-time", example="2022-01-01T00:00:00Z")
     * )
     */

}
