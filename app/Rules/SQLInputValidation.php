<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SQLInputValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $first_letter = str($value)->substr(0, 1)->toString();

        if ($first_letter === '=' ||
            $first_letter === '+' ||
            $first_letter === '-' ||
            $first_letter === '@') {
            $fail('Invalid characters given. Characters such as =, +, -, @  are not allowed');
        }
    }
}
