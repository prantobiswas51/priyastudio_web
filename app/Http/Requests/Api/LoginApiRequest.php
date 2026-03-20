<?php

namespace App\Http\Requests\Api;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = Str::of((string) $this->input('email'))
            ->trim()
            ->lower()
            ->value();

        $password = (string) $this->input('password', '');
        $password = preg_replace('/[\x00-\x1F\x7F]/u', '', $password) ?? '';

        $deviceName = Str::of((string) $this->input('device_name', 'mobile-app'))
            ->trim()
            ->substr(0, 100)
            ->value();

        $this->merge([
            'email' => $email,
            'password' => $password,
            'device_name' => $deviceName,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()) . '|' . $this->ip());
    }
}
