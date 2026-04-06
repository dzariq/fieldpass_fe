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
        $playerId = $this->route('id') ?? $this->route('player') ?? auth()->guard('player')->id();

        return [
            'name' => 'required|max:50',
            'email' => 'nullable|max:100|email|unique:players,email,' . $playerId,
            'username' => 'nullable|max:100|unique:players,username,' . $playerId,
            'password' => 'nullable|min:6|confirmed',
            'country_code' => 'nullable|string|max:4|regex:/^\d{1,4}$/',
            'phone' => 'nullable|max:20',
        ];
    }
}
