<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminAgendaCustomerLookupRequest;
use App\Http\Requests\AdminAgendaProfessionalLookupRequest;
use App\Http\Resources\AdminAgendaCustomerLookupResource;
use App\Http\Resources\AdminAgendaProfessionalLookupResource;
use App\Http\Resources\AdminAgendaServiceLookupResource;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminAgendaLookupController
{
    public function customers(AdminAgendaCustomerLookupRequest $request): AnonymousResourceCollection
    {
        $query = (string) $request->validated('q');

        return AdminAgendaCustomerLookupResource::collection(Customer::query()
            ->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%"))
            ->orderBy('name')
            ->orderBy('id')
            ->limit(20)
            ->get(['id', 'name', 'phone']));
    }

    public function services(Request $request): AnonymousResourceCollection
    {
        return AdminAgendaServiceLookupResource::collection(Service::query()
            ->with('category:id,name')
            ->where('active', true)
            ->whereHas('category', fn ($query) => $query->where('active', true))
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'service_category_id', 'name', 'duration_minutes', 'pricing_type', 'price']));
    }

    public function professionals(AdminAgendaProfessionalLookupRequest $request): AnonymousResourceCollection
    {
        return AdminAgendaProfessionalLookupResource::collection(Professional::query()
            ->where('active', true)
            ->when($request->validated('service_id'), fn ($query, $serviceId) => $query->whereHas(
                'services',
                fn ($services) => $services->whereKey($serviceId),
            ))
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']));
    }
}
