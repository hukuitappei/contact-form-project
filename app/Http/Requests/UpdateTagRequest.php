<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tags', 'name')->ignore($this->route('tag')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '名前は必須です。',
            'name.max' => '名前は50文字以内で入力してください。',
            'name.unique' => 'この名前は既に使用されています。',
        ];
    }
}
