<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $orderId = $this->route('order');

        $commonRules = [
            'user_id' => 'nullable|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ];

        if ($this->method() === 'POST') {
            $postRules = [
                // No unique order_number on create, it's generated
            ];
            return array_merge($commonRules, $postRules);
        }

        if ($this->method() === 'PATCH' || $this->method() === 'PUT') {
            $updateRules = [
                // No unique order_number on update
            ];
            return array_merge($commonRules, $updateRules);
        }

        return [];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'لابد من إضافة عناصر للطلب.',
            'items.array' => 'صيغة العناصر غير صحيحة.',
            'items.*.product_id.required' => 'لابد من تحديد المنتج.',
            'items.*.quantity.required' => 'لابد من تحديد الكمية.',
            'items.*.unit_price.required' => 'لابد من تحديد سعر الوحدة.',
            'items.*.subtotal.required' => 'لابد من تحديد الإجمالي الفرعي.'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors' => $validator->errors()
        ], 422));
    }
}