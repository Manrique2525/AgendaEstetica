<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Appointment;
use App\Support\AdminAgendaRange;
use Illuminate\Database\Eloquent\Collection;

final class ListAdminAgendaAppointments
{
    /**
     * @param  array{from: string, to: string, professional_id?: int, status?: string, service_id?: int, customer_id?: int}  $filters
     */
    public function execute(array $filters): Collection
    {
        $range = (new AdminAgendaRange)->resolve($filters['from'], $filters['to']);

        return Appointment::query()
            ->with(['customer', 'service.category', 'professional'])
            ->where('starts_at', '<', $range['to'])
            ->where('ends_at', '>', $range['from'])
            ->when(isset($filters['professional_id']), fn ($query) => $query->where('professional_id', $filters['professional_id']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['service_id']), fn ($query) => $query->where('service_id', $filters['service_id']))
            ->when(isset($filters['customer_id']), fn ($query) => $query->where('customer_id', $filters['customer_id']))
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();
    }
}
