<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\Analytics\AnalyticsService;
use App\Actions\Contact\StoreContactMessage;
use App\Mail\ContactMessageMail;
use App\Notifications\NewContactMessageNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ContactMessageController extends Controller
{

    
    /**
     * @OA\Post(
     *     path="/api/contact-messages",
     *     summary="Store a new contact message",
     *     tags={"Contact Messages"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "message"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="message", type="string", example="I have a question about your services."),
     *             @OA\Property(property="project_type", type="string", example="development"),
     *             @OA\Property(property="services", type="array", @OA\Items(type="string", example="web_development")),
     *             @OA\Property(property="attachment", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact message created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Your message has been sent successfully!"),
     *             @OA\Property(property="data", ref="#/components/schemas/ContactMessage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="message", type="string", example="Validation failed for some fields.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while processing your request.")
     *         )
     *     )
     * )
     */
    public function store(StoreContactMessageRequest $request, StoreContactMessage $store): JsonResponse
    {
        // Validate the request data using StoreContactMessageRequest
        $data = $request->validated();

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store(
                'contact-attachments', 'public'
            );
        }

        // Store the contact message
        $contactMessage = $store->execute(array_merge($data, [
            'attachment_path' => $attachmentPath,
        ]), $request->ip());

        // Track the contact submission event
        app(AnalyticsService::class)->track('contact_submitted', [
            'entity_type' => 'contact_message',
            'entity_id' => $contactMessage->id,
            'source' => 'contact_form',
        ]);

        // Send an email to the admin
        $this->sendEmailToAdmin($contactMessage);

        // Notify admins
        $this->notifyAdmins($contactMessage);

        // Return the response
        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully!',
            'data' => $contactMessage,
        ], 201);
    }

    /**
     * Send an email to the admin.
     *
     * @param \App\Models\ContactMessage $contactMessage
     * @return void
     */
    private function sendEmailToAdmin(ContactMessage $contactMessage): void
    {
        $to = settings('contact.email_to'); // Get the admin's email from the settings

        if ($to) {
            Mail::to($to)->send(new ContactMessageMail($contactMessage)); // Send email to admin
        }
    }

    /**
     * Notify the admins via notifications.
     *
     * @param \App\Models\ContactMessage $contactMessage
     * @return void
     */
    private function notifyAdmins(ContactMessage $contactMessage): void
    {
        User::role(['admin', 'super-admin']) // Notify users with 'admin' or 'super-admin' role
            ->get()
            ->each(fn($user) => $user->notify(new NewContactMessageNotification($contactMessage)));
    }
}
