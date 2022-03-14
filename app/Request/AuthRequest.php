<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class AuthRequest extends FormRequest
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
        switch($this->getScene()) {
            case 'register':
                return [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'password' => ['required', 'string', 'min:6'],
                ];
            case 'login':
                return [
                    'email' => ['required', 'string', 'email'],
                    'password' => ['required', 'string', 'min:6'],
                ];
            default:
                return [];
        }
    }
}
