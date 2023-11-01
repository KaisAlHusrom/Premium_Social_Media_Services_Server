<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OnlyArabic implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!empty($value) && !preg_match('/^[\p{Arabic}z0-9\s]+$/u', $value)) {
            $fail("$attribute must contain only Arabic letters");
        }
    }
}
