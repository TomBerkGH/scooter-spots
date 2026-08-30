<?php

namespace App\Http\Requests;

use App\Data\UpdateSpotData;
use App\Enums\SpotImageType;
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
        $imageTypes = implode(',', array_map(
            static fn (SpotImageType $type): string => $type->value,
            SpotImageType::cases(),
        ));

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array:'.$imageTypes],
            'images.*' => ['nullable', 'string', 'max:2100000', 'regex:/^data:image\/(?:apng|avif|bmp|gif|jpeg|png|svg\+xml|vnd\.microsoft\.icon|webp|x-icon)(?:;[^,]*)*;base64,/i'],
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tags.required' => 'Kies minimaal één tag.',
            'tags.min' => 'Kies minimaal één tag.',
        ];
    }

    public function toData(): UpdateSpotData
    {
        return UpdateSpotData::fromArray($this->validated());
    }
}
