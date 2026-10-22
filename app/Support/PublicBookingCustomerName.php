<?php

declare(strict_types=1);

namespace App\Support;

final class PublicBookingCustomerName
{
    public function canonicalize(string $name): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);

        return mb_strtolower($collapsed, 'UTF-8');
    }

    public function equivalent(string $left, string $right): bool
    {
        return $this->canonicalize($left) === $this->canonicalize($right);
    }
}
