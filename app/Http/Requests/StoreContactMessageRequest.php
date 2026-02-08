<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // أو اضبطها حسب الحاجة
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'project_type' => ['required', 'string', 'max:255'],
            'services' => ['required', 'array'],
            'services.*' => ['string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:15360', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }
}
