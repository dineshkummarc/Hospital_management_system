@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User Profile')
@section('breadcrumb')
<a href="{{ route('admin.users.index') }}">Users</a> / Edit Profile
@endsection

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">📝 Edit User Profile ({{ $user->name }})</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="form-grid">
                {{-- Username (Disabled) --}}
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" value="{{ $user->username }}" class="form-control" style="opacity:0.6; cursor:not-allowed;" readonly disabled>
                </div>

                {{-- Full Name --}}
                <div class="form-group">
                    <label class="form-label" for="name">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') error @enderror" required>
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') error @enderror">
                    @error('email')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Phone --}}
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') error @enderror" required>
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
                        <option value="staff" {{ in_array(old('role', $user->role), ['staff', 'member']) ? 'selected' : '' }}>Staff</option>
                        <option value="doctor" {{ in_array(old('role', $user->role), ['doctor', 'coach']) ? 'selected' : '' }}>Doctor</option>
                        <option value="receptionist" {{ in_array(old('role', $user->role), ['receptionist', 'treasurer']) ? 'selected' : '' }}>Receptionist</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('role')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Profile Picture (Avatar) --}}
                <div style="display:flex; align-items:center; gap:20px; margin-top: 15px;">
                    <img src="{{ $user->avatar_url }}" style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid var(--glass-border);">
                    <div style="flex:1;">
                        <label class="form-label" for="avatar">Change Profile Picture</label>
                        <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') error @enderror" accept="image/*">
                        @error('avatar')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="auth-checkbox-label" style="display:flex; align-items:center; gap:8px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <span>Account Active & Enabled</span>
                </label>
            </div>

            {{-- Optional Password Change --}}
            <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--glass-border); border-radius: var(--radius-sm); padding: 18px; margin: 24px 0 16px;">
                <span class="font-bold text-sm" style="display:block; margin-bottom:12px; color: var(--gold-400);">🔑 Reset Security Password (Optional)</span>
                
                <div class="form-grid">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') error @enderror" placeholder="Leave blank to keep current">
                        @error('password')
                        <div class="form-error" style="margin-top:4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-between" style="margin-top: 24px;">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
