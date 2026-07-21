@extends('layouts.app')
@section('title', 'Add System User')
@section('page-title', 'Add System User')
@section('breadcrumb')
<a href="{{ route('admin.users.index') }}">Users</a> / Add User
@endsection

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">📝 Add New User Profile</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">
                {{-- Username --}}
                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="required">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" class="form-control @error('username') error @enderror" placeholder="e.g. jdoe" required>
                    @error('username')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Full Name --}}
                <div class="form-group">
                    <label class="form-label" for="name">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') error @enderror" placeholder="e.g. John Doe" required>
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') error @enderror" placeholder="e.g. staff@hospital.com">
                    @error('email')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Phone --}}
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') error @enderror" placeholder="e.g. 0712345678" required>
                    @error('phone')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                {{-- System Role --}}
                <div class="form-group">
                    <label class="form-label" for="role">System Role <span class="required">*</span></label>
                    <select name="role" id="role" class="form-control @error('role') error @enderror" required>
                        <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="doctor" {{ old('role') === 'doctor' ? 'selected' : '' }}>Doctor</option>
                        <option value="receptionist" {{ old('role') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('role')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Profile Picture (Avatar) --}}
                <div class="form-group">
                    <label class="form-label" for="avatar">Profile Picture (Avatar)</label>
                    <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') error @enderror" accept="image/*">
                    @error('avatar')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="required">*</span></label>
                    <input type="password" name="password" id="password" class="form-control @error('password') error @enderror" required>
                    @error('password')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password Confirmation --}}
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm Password <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-between" style="margin-top: 24px;">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>
@endsection
