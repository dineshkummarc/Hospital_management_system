@extends('layouts.app')
@section('title', 'System Users')
@section('page-title', 'User Management & Privileges')

@section('content')
{{-- Users Stats --}}
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(5, 1fr); gap:12px; margin-bottom:28px;">
    <div class="stat-card" style="background:var(--navy-800); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:12px;">
        <div style="font-size:28px; opacity:0.12;">👥</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--text-primary);">{{ $stats['total'] }}</div>
            <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:0.5px; font-size:10px;">Total Users</div>
        </div>
    </div>
    <div class="stat-card" style="background:var(--navy-800); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:12px;">
        <div style="font-size:28px; opacity:0.12;">👑</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--gold-400);">{{ $stats['admins'] }}</div>
            <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:0.5px; font-size:10px;">Admins</div>
        </div>
    </div>
    <div class="stat-card" style="background:var(--navy-800); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:12px;">
        <div style="font-size:28px; opacity:0.12;">🩺</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--emerald-400);">{{ $stats['doctors'] }}</div>
            <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:0.5px; font-size:10px;">Doctors</div>
        </div>
    </div>
    <div class="stat-card" style="background:var(--navy-800); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:12px;">
        <div style="font-size:28px; opacity:0.12;">📋</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--blue-400);">{{ $stats['receptionists'] }}</div>
            <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:0.5px; font-size:10px;">Receptionists</div>
        </div>
    </div>
    <div class="stat-card" style="background:var(--navy-800); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:12px;">
        <div style="font-size:28px; opacity:0.12;">🏥</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--text-secondary);">{{ $stats['staff'] }}</div>
            <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:0.5px; font-size:10px;">Staff</div>
        </div>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">🔍 Search & Filter Users</span>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            + Add New User
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 100px; gap:16px; align-items: end;">
            <div>
                <label class="form-label">Search Query</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, phone, username...">
            </div>
            <div>
                <label class="form-label">System Role</label>
                <select name="role" class="form-control">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="doctor" {{ in_array(request('role'), ['doctor', 'coach']) ? 'selected' : '' }}>Doctor</option>
                    <option value="receptionist" {{ in_array(request('role'), ['receptionist', 'treasurer']) ? 'selected' : '' }}>Receptionist</option>
                    <option value="staff" {{ in_array(request('role'), ['staff', 'member']) ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                    Search
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">👥 User Directory</span>
    </div>
    <div class="table-wrap">
        @if($members->count())
        <table>
            <thead>
                <tr>
                    <th>User Profile</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $m)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="{{ $m->avatar_url }}" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:1.5px solid var(--glass-border);">
                            <div>
                                <strong style="display:block; font-size:14px; color:var(--text-primary);">{{ $m->name }}</strong>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm">@ {{ $m->username }}</td>
                    <td class="text-sm">{{ $m->phone }}</td>
                    <td class="text-sm">{{ $m->email ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $m->role_color }}">{{ $m->role_label }}</span>
                    </td>
                    <td>
                        @if($m->is_active)
                        <span class="badge badge-green">Active</span>
                        @else
                        <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <div class="d-flex justify-end" style="gap:6px; justify-content: flex-end;">
                            <a href="{{ route('admin.users.edit', $m) }}" class="btn btn-primary btn-sm" style="padding:4px 8px;">
                                Edit
                            </a>
                            @if($m->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $m) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" style="padding:4px 8px; min-width: 75px;">
                                    {{ $m->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <div class="empty-state-title">No users found matching search criteria</div>
        </div>
        @endif
    </div>
    @if($members->hasPages())
    <div class="card-footer">
        {{ $members->links() }}
    </div>
    @endif
</div>
@endsection
