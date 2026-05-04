@extends('admin.layout')

@section('title', 'Work Sites')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Work Sites</h2>
    <a href="{{ route('admin.sites.create') }}" class="btn btn-primary">
        <i class="bi bi-geo-alt me-1"></i> Add Site
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Coordinates</th>
                        <th>Radius</th>
                        <th>Policy</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                    <tr>
                        <td class="ps-4 fw-semibold">
                            {{ $site->name }}
                            <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $site->address }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark font-monospace border">
                                {{ number_format($site->latitude, 6) }}, {{ number_format($site->longitude, 6) }}
                            </span>
                        </td>
                        <td>{{ $site->radius }} m</td>
                        <td>
                            @if($site->policy == 'warn')
                                <span class="badge bg-warning text-dark">Warn</span>
                            @else
                                <span class="badge bg-danger">Reject</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.sites.destroy', $site) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this site?');">
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
                        <td colspan="5" class="text-center py-4 text-muted">No work sites configured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sites->hasPages())
    <div class="card-footer bg-white pt-3 pb-0">
        {{ $sites->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
