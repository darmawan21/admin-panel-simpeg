@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Dashboard Overview</h2>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">Total Employees</h6>
                <h2 class="mb-0 fw-bold">{{ $totalEmployees }}</h2>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">Today's Attendances</h6>
                <h2 class="mb-0 fw-bold">{{ $todayAttendances }}</h2>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">System Status</h6>
                <h2 class="mb-0 fw-bold text-success">Online</h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
        <h5 class="fw-bold mb-0">Recent Clock-ins/outs</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td>{{ $log->employee->name }} <br><small class="text-muted">{{ $log->employee->employee_id }}</small></td>
                        <td>
                            @if($log->type == 'clock_in')
                                <span class="badge bg-primary">Clock In</span>
                            @else
                                <span class="badge bg-secondary">Clock Out</span>
                            @endif
                        </td>
                        <td>{{ $log->device_timestamp->format('d M Y, H:i') }}</td>
                        <td>
                            @if($log->verified)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td>{{ number_format($log->verification_score, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No attendance records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
