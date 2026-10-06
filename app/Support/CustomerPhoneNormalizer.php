<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class CustomerPhoneNormalizer
{
    public function normalize(string $input): string
    {
        $value = trim($input);
        $value = str_replace([' ', '-', '(', ')', '.'], '', $value);

        if ($value === '') {
            throw new InvalidArgumentException('Phone number cannot be empty.');
        }

        if (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }

        if (str_starts_with($value, '+')) {
            $digits = substr($value, 1);

            if ($digits === '' || ! ctype_digit($digits) || strlen($digits) > 15) {
                throw new InvalidArgumentException('Phone number must contain 1 to 15 digits after the prefix.');
            }

            return '+'.$digits;
        }

        if (! ctype_digit($value)) {
            throw new InvalidArgumentException('Phone number contains invalid characters.');
        }

        if (strlen($value) === 10) {
            return '+52'.$value;
        }

        if (strlen($value) === 12 && str_starts_with($value, '52')) {
            return '+'.$value;
        }

        throw new InvalidArgumentException('Unprefixed phone number format is ambiguous.');
    }
}
