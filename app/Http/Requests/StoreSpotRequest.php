<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'image' => ['required', 'string', 'max:2100000', 'regex:/^data:image\/(jpeg|png);base64,/'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.max' => 'De verwerkte foto is te groot. Kies de foto opnieuw.',
            'image.regex' => 'De gekozen foto kon niet worden verwerkt.',
        ];
    }
}
