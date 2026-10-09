<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\MaintenanceRecord;
use App\Models\SoftwareProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Step 12: Management Systems and Technology's list of requests now
     * in ongoing Monitoring & Maintenance. There's no further status to
     * advance to — this is the flowchart's END state.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::MonitoringMaintenance->value)
            ->orderBy('updated_at')
            ->get();

        return view('workflow.monitoring.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load(['monitoringRecords', 'maintenanceRecords', 'kpis']);

        return view('workflow.monitoring.show', [
            'project' => $softwareProject,
        ]);
    }

    public function storeMonitoringRecord(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageMonitoring', $softwareProject);

        $validated = $request->validate([
            'record_number' => ['required', 'string', 'max:50'],
            'monitoring_date' => ['required', 'date'],
            'monitoring_type' => ['required', 'string', 'max:50'],
            'uptime_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'average_response_time_ms' => ['nullable', 'numeric', 'min:0'],
            'active_users' => ['nullable', 'integer', 'min:0'],
            'total_users' => ['nullable', 'integer', 'min:0'],
            'incidents_count' => ['nullable', 'integer', 'min:0'],
            'performance_observations' => ['nullable', 'string', 'max:4000'],
        ]);

        $softwareProject->monitoringRecords()->create([
            'id' => Str::uuid(),
            'record_number' => $validated['record_number'],
            'monitoring_date' => $validated['monitoring_date'],
            'monitoring_type' => $validated['monitoring_type'],
            'uptime_percentage' => $validated['uptime_percentage'] ?? null,
            'average_response_time_ms' => $validated['average_response_time_ms'] ?? null,
            'active_users' => $validated['active_users'] ?? null,
            'total_users' => $validated['total_users'] ?? null,
            'incidents_count' => $validated['incidents_count'] ?? 0,
            'performance_observations' => $validated['performance_observations'] ?? null,
            'recorded_by' => $request->user()->id,
            'recorded_at' => now(),
        ]);

        return redirect()
            ->route('workflow.monitoring.show', $softwareProject)
            ->with('success', 'Monitoring record added.');
    }

    public function storeKpi(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageMonitoring', $softwareProject);

        $validated = $request->validate([
            'kpi_code' => ['required', 'string', 'max:50'],
            'kpi_name' => ['required', 'string', 'max:255'],
            'measurement_period' => ['nullable', 'string', 'max:50'],
            'target_value' => ['nullable', 'numeric'],
            'actual_value' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(['MET', 'NOT_MET', 'IN_PROGRESS'])],
        ]);

        $softwareProject->kpis()->create([
            'id' => Str::uuid(),
            'kpi_code' => $validated['kpi_code'],
            'kpi_name' => $validated['kpi_name'],
            'measurement_period' => $validated['measurement_period'] ?? null,
            'target_value' => $validated['target_value'] ?? null,
            'actual_value' => $validated['actual_value'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'status' => $validated['status'] ?? null,
            'recorded_by' => $request->user()->id,
            'recorded_at' => now(),
        ]);

        return redirect()
            ->route('workflow.monitoring.show', $softwareProject)
            ->with('success', 'KPI recorded.');
    }

    public function storeMaintenanceRequest(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('reportMaintenance', $softwareProject);

        $validated = $request->validate([
            'maintenance_type' => ['required', Rule::in(['CORRECTIVE', 'PREVENTIVE', 'ENHANCEMENT', 'ADAPTIVE'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'priority' => ['nullable', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
        ]);

        $maintenanceNumber = 'MR-' . str_pad((string) ($softwareProject->maintenanceRecords()->count() + 1), 3, '0', STR_PAD_LEFT);

        $softwareProject->maintenanceRecords()->create([
            'id' => Str::uuid(),
            'maintenance_number' => $maintenanceNumber,
            'maintenance_type' => $validated['maintenance_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'reason' => $validated['reason'] ?? null,
            'priority' => $validated['priority'] ?? 'MEDIUM',
            'status' => 'OPEN',
            'reported_by' => $request->user()->id,
            'reported_at' => now(),
        ]);

        return redirect()
            ->route('workflow.monitoring.show', $softwareProject)
            ->with('success', "{$maintenanceNumber} reported.");
    }

    public function updateMaintenanceStatus(
        Request $request,
        SoftwareProject $softwareProject,
        MaintenanceRecord $maintenanceRecord
    ): RedirectResponse {
        $this->authorize('manageMonitoring', $softwareProject);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['IN_PROGRESS', 'RESOLVED', 'CLOSED'])],
            'resolution' => ['required_if:status,RESOLVED', 'nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $attributes = ['status' => $validated['status']];

        if (isset($validated['assigned_to'])) {
            $attributes['assigned_to'] = $validated['assigned_to'];
        }

        if ($validated['status'] === 'IN_PROGRESS' && ! $maintenanceRecord->started_at) {
            $attributes['started_at'] = now();
        }

        if ($validated['status'] === 'RESOLVED') {
            $attributes['resolution'] = $validated['resolution'];
            $attributes['completed_at'] = now();
        }

        $maintenanceRecord->update($attributes);

        return redirect()
            ->route('workflow.monitoring.show', $softwareProject)
            ->with('success', "{$maintenanceRecord->maintenance_number} updated.");
    }
}
