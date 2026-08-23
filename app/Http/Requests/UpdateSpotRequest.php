<?php

namespace App\Http\Requests;

use App\Data\UpdateSpotData;
use App\Models\Spot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSpotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $spot = $this->route('spot');

        return $this->user() !== null
            && $spot instanceof Spot
            && $spot->user_id === $this->user()->id;
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
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }

    public function toData(): UpdateSpotData
    {
        return UpdateSpotData::fromArray($this->validated());
    }
}
