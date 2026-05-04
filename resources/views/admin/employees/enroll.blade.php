@extends('admin.layout')

@section('title', 'Enroll Face')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Enroll Face: {{ $employee->name }}</h2>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Instructions</h5>
                <ul class="text-muted">
                    <li>Upload 3 to 5 clear photos of the employee.</li>
                    <li>Ensure the face is well-lit and directly facing the camera.</li>
                    <li>Do not wear sunglasses, masks, or hats.</li>
                    <li>The system will extract the face from each photo and create a unique biometric profile.</li>
                </ul>
                <div class="mt-4 p-3 bg-light rounded text-center">
                    <p class="mb-1 fw-semibold">Current Status:</p>
                    @if($employee->enrollment_status == 'enrolled')
                        <span class="badge bg-success py-2 px-3 fs-6"><i class="bi bi-check-circle me-1"></i> Enrolled</span>
                    @elseif($employee->enrollment_status == 'needs_update')
                        <span class="badge bg-warning text-dark py-2 px-3 fs-6">Needs Update</span>
                    @else
                        <span class="badge bg-secondary py-2 px-3 fs-6">Pending</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.employees.enroll.store', $employee) }}" method="POST" enctype="multipart/form-data" id="enrollmentForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="photos" class="form-label fw-semibold">Upload Photos (Min 1, Max 5)</label>
                        <input class="form-control @error('photos') is-invalid @enderror" type="file" id="photos" name="photos[]" multiple accept="image/*" required>
                        <div class="form-text">Supported formats: JPG, PNG. Max 5MB per image.</div>
                        @error('photos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div id="imagePreview" class="d-flex flex-wrap gap-2 mb-4">
                        <!-- Image previews will be injected here via JS -->
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-person-bounding-box me-2"></i> Process Face Enrollment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('photos').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('imagePreview');
        previewContainer.innerHTML = '';
        
        if (this.files.length > 5) {
            alert('You can only upload a maximum of 5 photos.');
            this.value = '';
            return;
        }

        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.height = '120px';
                img.style.width = '120px';
                img.style.objectFit = 'cover';
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    });

    document.getElementById('enrollmentForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
    });
</script>
@endsection
