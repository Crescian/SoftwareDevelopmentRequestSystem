<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Read-only org directory (Business Unit -> Company -> Department) served
 * by the Support Request System's /org/* endpoints. Every call upserts the
 * returned rows into this app's local mirror tables, the same read-through
 * idiom TicketingIdentityService uses for users — so every existing
 * `requestingDepartment` reference keeps working against a local FK.
 */
class OrgDirectoryService
{
    private const CACHE_TTL_MINUTES = 10;

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    public function businessUnits(): Collection
    {
        return Cache::remember('org-directory.business-units', now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            $rows = $this->client()->get('org/business-units')->throw()->json();

            foreach ($rows as $row) {
                BusinessUnit::updateOrCreate(
                    ['id' => $row['id']],
                    ['name' => $row['name'], 'last_synced_at' => now()],
                );
            }

            return collect($rows)->map(fn (array $row) => ['id' => $row['id'], 'name' => $row['name']])->values();
        });
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    public function companies(string $businessUnitId): Collection
    {
        return Cache::remember("org-directory.companies.{$businessUnitId}", now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($businessUnitId) {
            $rows = $this->client()
                ->get('org/companies', ['business_unit_id' => $businessUnitId])
                ->throw()
                ->json();

            foreach ($rows as $row) {
                Company::updateOrCreate(
                    ['id' => $row['id']],
                    [
                        'name' => $row['name'],
                        'business_unit_id' => $row['business_unit_id'] ?? $businessUnitId,
                        'last_synced_at' => now(),
                    ],
                );
            }

            return collect($rows)->map(fn (array $row) => ['id' => $row['id'], 'name' => $row['name']])->values();
        });
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    public function departments(string $companyId): Collection
    {
        return Cache::remember("org-directory.departments.{$companyId}", now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($companyId) {
            $rows = $this->client()
                ->get('org/departments', ['company_id' => $companyId])
                ->throw()
                ->json();

            foreach ($rows as $row) {
                Department::updateOrCreate(
                    ['id' => $row['id']],
                    [
                        'code' => $row['id'],
                        'name' => $row['name'],
                        'is_active' => true,
                        'company_id' => $row['company_id'] ?? $companyId,
                        'business_unit_id' => $row['business_unit_id'] ?? null,
                        'last_synced_at' => now(),
                    ],
                );
            }

            return collect($rows)->map(fn (array $row) => ['id' => $row['id'], 'name' => $row['name']])->values();
        });
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(config('services.ticketing.base_url'))
            ->withToken(config('services.ticketing.org_token'))
            ->acceptJson()
            ->timeout((int) config('services.ticketing.timeout', 10));
    }
}
