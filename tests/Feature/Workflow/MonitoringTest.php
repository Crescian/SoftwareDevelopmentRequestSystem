<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function projectInMonitoring(User $requestor): SoftwareProject
    {
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'MONITORING_MAINTENANCE',
        ]);
    }

    public function test_non_mst_user_cannot_record_monitoring_data_or_kpis(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)
            ->post(route('workflow.monitoring.monitoring-records.store', $project), [
                'record_number' => 'MON-001',
                'monitoring_date' => now()->toDateString(),
                'monitoring_type' => 'UPTIME',
            ])
            ->assertForbidden();
    }

    public function test_mst_can_record_monitoring_data_and_kpis(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($mst)->post(route('workflow.monitoring.monitoring-records.store', $project), [
            'record_number' => 'MON-001',
            'monitoring_date' => now()->toDateString(),
            'monitoring_type' => 'UPTIME',
            'uptime_percentage' => 99.95,
            'incidents_count' => 0,
        ])->assertRedirect();

        $this->actingAs($mst)->post(route('workflow.monitoring.kpis.store', $project), [
            'kpi_code' => 'KPI-01',
            'kpi_name' => 'User Adoption',
            'target_value' => 100,
            'actual_value' => 80,
            'status' => 'IN_PROGRESS',
        ])->assertRedirect();

        $fresh = $project->fresh();
        $this->assertCount(1, $fresh->monitoringRecords);
        $this->assertCount(1, $fresh->kpis);
    }

    public function test_any_project_viewer_can_report_a_maintenance_request(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $response = $this->actingAs($requestor)->post(
            route('workflow.monitoring.maintenance-records.store', $project),
            [
                'maintenance_type' => 'CORRECTIVE',
                'title' => 'Export button broken',
                'description' => 'CSV export throws a 500 error.',
            ],
        );

        $response->assertRedirect(route('workflow.monitoring.show', $project));

        $fresh = $project->fresh();
        $this->assertCount(1, $fresh->maintenanceRecords);
        $this->assertSame('OPEN', $fresh->maintenanceRecords->first()->status);
        $this->assertSame($requestor->id, $fresh->maintenanceRecords->first()->reported_by);
    }

    public function test_someone_with_no_relation_to_the_project_cannot_report_or_view(): void
    {
        $requestor = User::factory()->create();
        $unrelated = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($unrelated)
            ->post(route('workflow.monitoring.maintenance-records.store', $project), [
                'maintenance_type' => 'CORRECTIVE',
                'title' => 'Bug',
                'description' => 'Something broke.',
            ])
            ->assertForbidden();
    }

    public function test_mst_resolving_a_maintenance_request_requires_resolution_text(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.monitoring.maintenance-records.store', $project), [
            'maintenance_type' => 'CORRECTIVE',
            'title' => 'Export button broken',
            'description' => 'CSV export throws a 500 error.',
        ]);

        $record = $project->fresh()->maintenanceRecords->first();

        $this->actingAs($mst)
            ->post(route('workflow.monitoring.maintenance-records.status', [$project, $record]), ['status' => 'RESOLVED'])
            ->assertSessionHasErrors('resolution');

        $this->actingAs($mst)->post(
            route('workflow.monitoring.maintenance-records.status', [$project, $record]),
            ['status' => 'RESOLVED', 'resolution' => 'Fixed the CSV encoding bug.'],
        )->assertRedirect();

        $fresh = $record->fresh();
        $this->assertSame('RESOLVED', $fresh->status);
        $this->assertNotNull($fresh->completed_at);

        // The project itself never leaves Monitoring & Maintenance.
        $this->assertSame('MONITORING_MAINTENANCE', $project->fresh()->status);
    }
}
