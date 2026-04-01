<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayerTrainingRequest extends FormRequest
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
        return [
            'player_trainings' => 'required|array',
            'player_trainings.*.player_id' => 'required|exists:players,id',
            'player_trainings.*.training_attribute_id' => 'required|exists:training_attributes,id',
            'player_trainings.*.start_date' => 'required|date',
            'player_trainings.*.end_date' => 'required|date|after_or_equal:player_trainings.*.start_date',
            'player_trainings.*.score' => 'nullable|numeric|min:0|max:100',
            'player_trainings.*.message' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check 5 training limit per player
            $playerTrainingCounts = [];
            
            foreach ($this->input('player_trainings', []) as $index => $training) {
                $playerId = $training['player_id'] ?? null;
                if ($playerId) {
                    $playerTrainingCounts[$playerId] = ($playerTrainingCounts[$playerId] ?? 0) + 1;
                    
                    if ($playerTrainingCounts[$playerId] > 5) {
                        $validator->errors()->add(
                            "player_trainings.{$index}.player_id", 
                            "Maximum 5 trainings allowed per player."
                        );
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'player_trainings.required' => 'At least one player training is required.',
            'player_trainings.*.player_id.required' => 'Player selection is required.',
            'player_trainings.*.training_attribute_id.required' => 'Training attribute selection is required.',
            'player_trainings.*.start_date.required' => 'Start date is required.',
            'player_trainings.*.end_date.required' => 'End date is required.',
            'player_trainings.*.end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'player_trainings.*.score.numeric' => 'Score must be a number.',
            'player_trainings.*.score.max' => 'Score cannot exceed 100.',
            'player_trainings.*.message.max' => 'Message cannot exceed 1000 characters.',
        ];
    }
}