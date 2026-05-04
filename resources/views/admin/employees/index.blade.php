@extends('admin.layout')

@section('title', 'Employees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Employees</h2>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Add Employee
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.employees.index') }}" method="GET" class="d-flex w-100 max-w-md">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Enrollment</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $employee->employee_id }}</td>
                        <td>
                            {{ $employee->name }}
                            <div class="text-muted small">{{ $employee->email }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $employee->role == 'admin' ? 'danger' : ($employee->role == 'supervisor' ? 'warning text-dark' : 'secondary') }}">
                                {{ ucfirst($employee->role) }}
                            </span>
                        </td>
                        <td>
                            @if($employee->enrollment_status == 'enrolled')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Enrolled</span>
                            @elseif($employee->enrollment_status == 'needs_update')
                                <span class="badge bg-warning text-dark">Needs Update</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($employee->is_active)
                                <span class="text-success"><i class="bi bi-circle-fill small me-1"></i>Active</span>
                            @else
                                <span class="text-danger"><i class="bi bi-circle-fill small me-1"></i>Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.employees.enroll.create', $employee) }}" class="btn btn-sm btn-outline-info" title="Enroll Face">
                                <i class="bi bi-person-bounding-box"></i>
                            </a>
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-white pt-3 pb-0">
        {{ $employees->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
