@extends('admin.layout')

@section('title', 'Login')

@section('content')
<div class="container" style="height: 100vh;">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">SIMPEG</h2>
                        <p class="text-muted">Face Recognition Admin Panel</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li><small>{{ $error }}</small></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Admin ID</label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
