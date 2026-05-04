@extends('admin.layout')

@section('title', 'Attendance Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Attendance Reports</h2>
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Print / Export
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white border-0">
            <div class="card-body py-3">
                <h6 class="card-title mb-1">Total Logs</h6>
                <h3 class="mb-0">{{ $summary['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white border-0">
            <div class="card-body py-3">
                <h6 class="card-title mb-1">Verified Clock-ins/outs</h6>
                <h3 class="mb-0">{{ $summary['verified'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white border-0">
            <div class="card-body py-3">
                <h6 class="card-title mb-1">Failed Verifications</h6>
                <h3 class="mb-0">{{ $summary['failed'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Location (Lat, Lng)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4">
                            <span class="d-block fw-semibold">{{ $log->device_timestamp->format('d M Y') }}</span>
                            <span class="text-muted small">{{ $log->device_timestamp->format('H:i:s') }}</span>
                        </td>
                        <td>
                            {{ $log->employee->name }}
                            <div class="text-muted small">{{ $log->employee->employee_id }}</div>
                        </td>
                        <td>
                            @if($log->type == 'clock_in')
                                <span class="badge bg-primary">Clock In</span>
                            @else
                                <span class="badge bg-secondary">Clock Out</span>
                            @endif
                        </td>
                        <td>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-map"></i> View Map
                            </a>
                        </td>
                        <td>
                            @if($log->verified)
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Verified</span>
                            @else
                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>
                            @endif
                            @if($log->synced_from_offline)
                                <div class="text-muted small"><i class="bi bi-cloud-arrow-up"></i> Offline Sync</div>
                            @endif
                        </td>
                        <td class="text-end pe-4 font-monospace">
                            {{ number_format($log->verification_score, 4) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No attendance logs found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white pt-3 pb-0">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
