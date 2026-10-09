@php
    $decisionLink = fn (array $item) => match ($item['type']) {
        'Management Approval' => route('approvals.management.show', $item['project']),
        'UAT Sign-off' => route('workflow.uat.show', $item['project']),
        default => route('workflow.requirements-design.show', $item['project']),
    };
@endphp

@if (($needsYourDecision ?? collect())->isNotEmpty())
    <div class="card-soft mb-4" id="needs-your-decision">
        <div class="section-head">
            <h2><i class="bi bi-exclamation-diamond-fill text-warning me-1"></i> Needs Your Decision</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-requests">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Type</th>
                        <th>Title / Ref</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($needsYourDecision as $item)
                        <tr>
                            <td class="ref">{{ $item['project']?->project_id }}</td>
                            <td>{{ $item['type'] }}</td>
                            <td class="title">{{ $item['title'] }}</td>
                            <td class="text-end">
                                @if ($item['project'])
                                    <a href="{{ $decisionLink($item) }}" class="view-link">Review</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
