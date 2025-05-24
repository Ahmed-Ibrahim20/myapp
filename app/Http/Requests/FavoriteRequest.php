<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FavoriteRequest extends FormRequest
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
        $favoriteId = $this->route('favorite');

        $commonRules = [
            'user_id' => 'nullable|exists:users,id',
            'product_id' => 'required|exists:products,id'
        ];

        if ($this->method() === 'POST') {
            $postRules = [
                'user_id' => [
                    'nullable',
                    'exists:users,id',
                    Rule::unique('favorites')->where(function ($query) {
                        return $query->where('product_id', $this->product_id);
                    })
                ],
                'product_id' => 'required|exists:products,id'
            ];
            return array_merge($commonRules, $postRules);
        }

        if ($this->method() === 'PATCH' || $this->method() === 'PUT') {
            $updateRules = [
                'user_id' => [
                    'nullable',
                    'exists:users,id',
                    Rule::unique('favorites')
                        ->where(function ($query) {
                            return $query->where('product_id', $this->product_id);
                        })
                        ->ignore($favoriteId)
                ],
                'product_id' => 'required|exists:products,id'
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
            'user_id.required' => 'حقل المستخدم مطلوب.',
            'user_id.exists' => 'المستخدم المحدد غير موجود.',
            'user_id.unique' => 'هذا المنتج مضاف بالفعل إلى المفضلة لهذا المستخدم.',
            'product_id.required' => 'حقل المنتج مطلوب.',
            'product_id.exists' => 'المنتج المحدد غير موجود.',
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
        if ($this->has('user_id')) {
            $this->merge([
                'user_id' => (int)$this->user_id
            ]);
        }

        if ($this->has('product_id')) {
            $this->merge([
                'product_id' => (int)$this->product_id
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'المستخدم',
            'product_id' => 'المنتج'
        ];
    }

    /**
     * Check if product is already in user's favorites
     */
    public function isAlreadyFavorite(): bool
    {
        return \App\Models\Favorite::where('user_id', $this->user_id)
            ->where('product_id', $this->product_id)
            ->exists();
    }
}