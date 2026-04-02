<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;

class PlayerRequest extends FormRequest
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
    public function rules()
    {
        $playerId = $this->route('player') ?? auth()->guard('player')->id();

        return [
            'name' => 'required|max:50',
            'identity_number' => [
                'required',
                'max:20',
                // 'regex:/^\d{6}-\d{2}-\d{4}$/',
                'unique:players,identity_number,' . $playerId
            ],
            'email' => 'nullable|max:100|email|unique:players,email,' . $playerId,
            'username' => 'nullable|max:100|unique:players,username,' . $playerId,
            'password' => 'nullable|min:6|confirmed',
            'country_code' => 'nullable|string|max:4|regex:/^\d{1,4}$/',
            'phone' => 'nullable|max:20',
        ];
    }

    public function messages()
    {
        return [
            'identity_number.regex' => 'IC number must be in format: XXXXXX-XX-XXXX (e.g., 900101-01-1234)',
            'identity_number.unique' => 'This IC number is already registered.',
        ];
    }
}
