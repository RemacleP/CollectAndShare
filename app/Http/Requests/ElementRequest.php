<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ElementRequest extends FormRequest
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
        return [
            'label'=> 'required',
            'description'=> 'required',
            'year_production'=> 'required|integer|min:0|max:'.date('Y'),
            'history'=> 'nullable',
            'condition'=> 'required',
            'collection_id' => 'required',
            'price'=> 'required|numeric|min:0',
            'quantity'=> 'required|integer|min:0',
            'slug'=> 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
            'delete_image' => ['nullable', 'boolean'],
        ];
    }
}
