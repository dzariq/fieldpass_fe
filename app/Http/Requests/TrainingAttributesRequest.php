<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingAttributesRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $clubId = Admin::find(auth()->id())?->primaryClubId();

        $idRules = ['nullable'];
        if ($clubId !== null) {
            $idRules[] = Rule::exists('training_attributes', 'id')->where('club_id', $clubId);
        }

        return [
            'attributes' => 'required|array|max:10',
            'attributes.*.id' => $idRules,
            'attributes.*.name' => 'required|string|max:255',
            'attributes.*.status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'attributes.required' => 'At least one training attribute is required.',
            'attributes.max' => 'Maximum 10 training attributes are allowed.',
            'attributes.*.name.required' => 'Attribute name is required.',
            'attributes.*.name.max' => 'Attribute name cannot exceed 255 characters.',
            'attributes.*.status.required' => 'Attribute status is required.',
            'attributes.*.status.in' => 'Status must be either active or inactive.',
        ];
    }

    /**
     * Get the validated attributes data
     */
    public function getAttributes(): array
    {
        return $this->input('attributes', []);
    }
}