@extends('layouts.app')
@section('title', 'User Details - ' . $user->name)
@section('page-title', 'User Profile')
@section('breadcrumb')
<a href="{{ route('admin.users.index') }}">Users</a> / {{ $user->name }}
@endsection

@section('content')
<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">👤 User Details</span>
    </div>
    <div class="card-body">
        <div style="display:flex; align-items:center; gap:20px; margin-bottom:24px;">
            <img src="{{ $user->avatar_url }}" style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:2px solid var(--glass-border);">
            <div>
                <h3 style="font-size:20px; font-weight:700; color:var(--text-primary); margin:0;">{{ $user->name }}</h3>
                <div style="margin-top:4px;">
                    <span class="badge badge-{{ $user->role_color }}">{{ $user->role_label }}</span>
                    @if($user->is_active)
                    <span class="badge badge-green">Active</span>
                    @else
                    <span class="badge badge-red">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Username</div>
                <div class="font-bold text-sm">@ {{ $user->username }}</div>
            </div>
            <div>
                <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Phone Number</div>
                <div class="font-bold text-sm">{{ $user->phone }}</div>
            </div>
            <div>
                <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Email Address</div>
                <div class="font-bold text-sm">{{ $user->email ?? 'Not set' }}</div>
            </div>
            <div>
                <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Created At</div>
                <div class="font-bold text-sm">{{ $user->created_at->format('d M Y') }}</div>
            </div>
        </div>

        <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</div>
@endsection
