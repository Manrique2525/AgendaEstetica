<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Carbon\CarbonImmutable;

final readonly class NotificationEligibility
{
    private function __construct(
        public bool $eligible,
        public ?NotificationType $type,
        public ?NotificationChannel $channel,
        public ?CarbonImmutable $occursAt,
        public string $reason,
    ) {}

    public static function eligible(
        NotificationType $type,
        ?CarbonImmutable $occursAt = null,
    ): self {
        return new self(true, $type, NotificationChannel::WHATSAPP, $occursAt, 'eligible');
    }

    public static function ineligible(string $reason): self
    {
        return new self(false, null, null, null, $reason);
    }
}
