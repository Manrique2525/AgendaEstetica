<?php

declare(strict_types=1);

use App\Actions\CancelAppointment;
use App\Actions\CompleteAppointment;
use App\Actions\CreateAppointment;
use App\Actions\MarkAppointmentNoShow;
use App\Actions\RescheduleAppointment;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$payload = json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR);
$ready = $argv[2];
$result = $argv[3];
$start = $argv[4];

$writeResult = static function (array $value) use ($result): void {
    file_put_contents($result, json_encode($value, JSON_THROW_ON_ERROR));
};

try {
    $app = require dirname(__DIR__, 3).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    touch($ready);

    $deadline = microtime(true) + 15;
    while (! file_exists($start) && microtime(true) < $deadline) {
        usleep(10000);
    }
    if (! file_exists($start)) {
        throw new RuntimeException('Concurrency start barrier timed out.');
    }

    $value = match ($payload['action']) {
        'create' => (new CreateAppointment)->execute(
            Customer::query()->findOrFail($payload['customer_id']),
            Service::query()->findOrFail($payload['service_id']),
            Professional::query()->findOrFail($payload['professional_id']),
            CarbonImmutable::parse($payload['starts_at'], 'UTC'),
            CarbonImmutable::parse($payload['ends_at'], 'UTC'),
        ),
        'reschedule' => (new RescheduleAppointment)->execute(
            Appointment::query()->findOrFail($payload['appointment_id']),
            Professional::query()->findOrFail($payload['professional_id']),
            CarbonImmutable::parse($payload['starts_at'], 'UTC'),
            CarbonImmutable::parse($payload['ends_at'], 'UTC'),
        ),
        'cancel' => (new CancelAppointment)->execute(Appointment::query()->findOrFail($payload['appointment_id'])),
        'complete' => (new CompleteAppointment)->execute(Appointment::query()->findOrFail($payload['appointment_id'])),
        'no_show' => (new MarkAppointmentNoShow)->execute(Appointment::query()->findOrFail($payload['appointment_id'])),
        default => throw new InvalidArgumentException('Unknown concurrency action.'),
    };

    $writeResult(['success' => true, 'status' => $value->status?->value]);
} catch (Throwable $exception) {
    $writeResult([
        'success' => false,
        'exception' => $exception::class,
        'message' => $exception->getMessage(),
        'code' => $exception->getCode(),
        'sqlstate' => $exception instanceof QueryException ? $exception->getSqlState() : null,
    ]);
}
