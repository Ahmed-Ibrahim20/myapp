<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
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
        $categoryId = $this->route('category');

        $commonRules = [
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'user_add_id' => 'nullable|exists:users,id'
        ];

        if ($this->method() === 'POST') {
            $postRules = [
                'name' => 'required|string|max:255|unique:categories,name'
            ];
            return array_merge($commonRules, $postRules);
        }

        if ($this->method() === 'PATCH' || $this->method() === 'PUT') {
            $updateRules = [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories')->ignore($categoryId)
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
            'name.required' => 'حقل الاسم مطلوب.',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرف.',
            'name.unique' => 'هذا الاسم مستخدم بالفعل.',
            'image.max' => 'يجب ألا يتجاوز اسم الصورة 255 حرف.',
            'user_add_id.exists' => 'المستخدم المضيف غير موجود.',
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

        if ($this->has('note') && empty($this->note)) {
            $this->merge([
                'note' => null
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم التصنيف',
            'note' => 'ملاحظات',
            'image' => 'صورة التصنيف'
        ];
    }
}