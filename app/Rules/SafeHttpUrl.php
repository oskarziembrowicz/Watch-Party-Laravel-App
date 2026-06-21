<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeHttpUrl implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {

        if ($value === null) {
            return;
        }

        $parts = parse_url($value);

        if (!$parts) {
            $fail("$attribute must be a valid URL.");
            return;
        }

        if (!in_array(
            $parts['scheme'] ?? '',
            ['http', 'https'],
            true
        )) {
            $fail("$attribute must use http or https.");
            return;
        }


        if (!isset($parts['host'])) {
            $fail("$attribute must contain a hostname.");
            return;
        }


        $ips = gethostbynamel($parts['host']);

        if (!$ips) {
            $fail("$attribute hostname cannot be resolved.");
            return;
        }


        foreach ($ips as $ip) {
            if (
                filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE |
                        FILTER_FLAG_NO_RES_RANGE
                ) === false
            ) {
                $fail("$attribute points to a private network.");
                return;
            }
        }
    }
}
