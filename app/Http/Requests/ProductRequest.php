<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Product;

class ProductRequest extends FormRequest
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
        $productId = $this->route('product');

        $commonRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'quantity' => 'required|integer|min:0',
            'type' => 'required|integer|in:0,1,2',
            'image' => 'required|string|max:255',
            'user_add_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:categories,id'
        ];

        if ($this->method() === 'POST') {
            $postRules = [
                'name' => 'required|string|max:255|unique:products,name'
            ];
            return array_merge($commonRules, $postRules);
        }

        if ($this->method() === 'PATCH' || $this->method() === 'PUT') {
            $updateRules = [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products')->ignore($productId)
                ]
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
            'name.required' => 'حقل اسم المنتج مطلوب.',
            'name.max' => 'يجب ألا يتجاوز اسم المنتج 255 حرف.',
            'name.unique' => 'اسم المنتج مستخدم بالفعل.',
            'description.string' => 'يجب أن يكون الوصف نصياً.',
            'price.required' => 'حقل السعر مطلوب.',
            'price.numeric' => 'يجب أن يكون السعر رقماً.',
            'price.min' => 'يجب أن يكون السعر أكبر من أو يساوي الصفر.',
            'price.max' => 'يجب ألا يتجاوز السعر 99,999,999.99.',
            'quantity.required' => 'حقل الكمية مطلوب.',
            'quantity.integer' => 'يجب أن تكون الكمية رقماً صحيحاً.',
            'quantity.min' => 'يجب أن تكون الكمية أكبر من أو تساوي الصفر.',
            'type.required' => 'حقل النوع مطلوب.',
            'type.integer' => 'يجب أن يكون النوع رقماً صحيحاً.',
            'type.in' => 'يجب أن يكون النوع 0 (غير محدد) أو 1 (كيلوجرام) أو 2 (قطعة).',
            'image.required' => 'حقل الصورة مطلوب.',
            'image.max' => 'يجب ألا يتجاوز مسار الصورة 255 حرف.',
            'user_add_id.exists' => 'المستخدم المضيف غير موجود.',
            'category_id.exists' => 'التصنيف المحدد غير موجود.'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }

        if ($this->has('price')) {
            $this->merge([
                'price' => number_format((float)$this->price, 2, '.', '')
            ]);
        }

        if ($this->has('description') && empty($this->description)) {
            $this->merge([
                'description' => null
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'description' => 'وصف المنتج',
            'price' => 'السعر',
            'quantity' => 'الكمية',
            'type' => 'نوع المنتج',
            'image' => 'صورة المنتج'
        ];
    }
}