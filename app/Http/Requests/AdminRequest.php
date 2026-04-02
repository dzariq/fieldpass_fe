<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Primary key of the admin being updated (null on create).
     * Route `admins/{admin}` uses model binding, so `route('admin')` may be an Admin model.
     */
    private function adminPrimaryKey(): ?int
    {
        $admin = $this->route('admin');
        if ($admin instanceof Model) {
            $key = $admin->getKey();

            return is_numeric($key) ? (int) $key : null;
        }

        if ($admin === null || $admin === '') {
            return null;
        }

        return is_numeric($admin) ? (int) $admin : null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $adminId = $this->adminPrimaryKey();

        $phoneUnique = Rule::unique('admins', 'phone');
        if ($adminId !== null) {
            $phoneUnique->ignore($adminId);
        }

        $usernameUnique = Rule::unique('admins', 'username');
        if ($adminId !== null) {
            $usernameUnique->ignore($adminId);
        }

        return [
            'name' => 'required|max:50',
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', $phoneUnique],
            'country_code' => 'required|max:5',
            'username' => array_merge(
                $adminId !== null ? ['required'] : ['nullable'],
                ['max:100', $usernameUnique]
            ),
            'password' => 'nullable|min:6|confirmed',
        ];
    }
}
