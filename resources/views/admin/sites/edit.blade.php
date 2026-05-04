@extends('admin.layout')

@section('title', 'Edit Work Site')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Edit Work Site: {{ $site->name }}</h2>
    <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm max-w-2xl">
    <div class="card-body p-4">
        <form action="{{ route('admin.sites.update', $site) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="name" class="form-label">Site Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $site->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Full Address <span class="text-danger">*</span></label>
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" required>{{ old('address', $site->address) }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                    <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $site->latitude) }}" required>
                    @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                    <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $site->longitude) }}" required>
                    @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="radius" class="form-label">Radius (metres) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('radius') is-invalid @enderror" id="radius" name="radius" value="{{ old('radius', $site->radius) }}" min="10" required>
                    <div class="form-text">Minimum 10 metres.</div>
                    @error('radius') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="policy" class="form-label">Geofence Policy <span class="text-danger">*</span></label>
                    <select class="form-select @error('policy') is-invalid @enderror" id="policy" name="policy" required>
                        <option value="warn" {{ old('policy', $site->policy) == 'warn' ? 'selected' : '' }}>Warn (Allow with flag)</option>
                        <option value="reject" {{ old('policy', $site->policy) == 'reject' ? 'selected' : '' }}>Reject (Block clock-in)</option>
                    </select>
                    @error('policy') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">Update Site</button>
            </div>
        </form>
    </div>
</div>
@endsection
