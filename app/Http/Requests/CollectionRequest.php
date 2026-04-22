<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // On laisse à true pour que la Policy (authorize dans le controller) prenne le relais
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'description'  => 'required|string',
            'image'        => 'nullable|image|max:2048',
            'club_id'      => 'required|exists:clubs,id', // Ajout crucial pour ton nouveau projet
            'club_user_id' => 'required',
            'categories'   => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ];
    }
}
