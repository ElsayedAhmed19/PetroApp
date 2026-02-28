<?php

namespace App\Rules;

use Closure;
use DateTimeImmutable;
use Illuminate\Contracts\Validation\ValidationRule;

class Iso8601 implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isValid = false;

        try {
            if (is_string($value)) {
                new DateTimeImmutable($value);

                $isValid = str_contains($value, 'T') &&
                    preg_match('/(Z|[+-]\d{2}:\d{2})$/', $value);
            }
        } catch (\Exception) {
            $isValid = false;
        }

        if (!$isValid) {
            $fail("The {$attribute} must be a valid ISO8601 datetime.");
        }
    }
}
