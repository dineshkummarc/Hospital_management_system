@extends('layouts.app')
@section('title', 'Hospital Admin Dashboard')
@section('page-title', 'Hospital Dashboard')

@push('styles')
<style>
.dash-hero {
    background: linear-gradient(135deg, var(--navy-700) 0%, var(--navy-800) 55%, rgba(13,110,253,0.07) 100%);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-xl);
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    position: relative; overflow: hidden;
}
.hero-greeting { font-size: 11px; color: var(--blue-400); font-weight: 700; letter-spacing: 1.5px; margin-bottom: 4px; text-transform: uppercase; }
.hero-name     { font-family: 'Inter', sans-serif; font-weight: 800; font-size: 28px; color: var(--text-primary); line-height: 1.1; }
.hero-sub      { font-size: 14px; color: var(--text-secondary); margin-top: 6px; }

.stats-grid-admin {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}
.stats-grid-admin-row2 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 28px;
}
.stat-tile {
    background: var(--navy-800);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 20px 22px;
    position: relative; overflow: hidden;
    transition: var(--transition); cursor: default;
}
.stat-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 36px rgba(0,0,0,0.45); }
.stat-tile-accent { position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 4px 0 0 4px; }
.stat-tile.emerald .stat-tile-accent { background: linear-gradient(180deg, var(--emerald-400), var(--emerald-600)); }
.stat-tile.blue    .stat-tile-accent { background: linear-gradient(180deg, #0d6efd, #0b5ed7); }
.stat-tile.purple  .stat-tile-accent { background: linear-gradient(180deg, #A78BFA, #7C3AED); }
.stat-tile.orange  .stat-tile-accent { background: linear-gradient(180deg, #FB923C, #EA580C); }
.stat-tile.yellow  .stat-tile-accent { background: linear-gradient(180deg, #FACC15, #CA8A04); }
.stat-tile.cyan    .stat-tile-accent { background: linear-gradient(180deg, #22D3EE, #0891B2); }

.stat-tile-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; }
.stat-tile-value { font-family: 'Inter', sans-serif; font-size: 26px; font-weight: 800; line-height: 1; }
.stat-tile.emerald .stat-tile-value { color: var(--emerald-400); }
.stat-tile.blue    .stat-tile-value { color: #0d6efd; }
.stat-tile.purple  .stat-tile-value { color: #A78BFA; }
.stat-tile.orange  .stat-tile-value { color: #FB923C; }
.stat-tile.yellow  .stat-tile-value { color: #FACC15; }
.stat-tile.cyan    .stat-tile-value { color: #22D3EE; }
.stat-tile-sub  { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

.dash-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
.table-wrap { overflow-x: auto; }
.table-premium { width: 100%; border-collapse: collapse; font-size: 13px; }
.table-premium thead th {
    padding: 12px 16px; font-size: 10px; font-weight: 700;
    letter-spacing: 1.2px; text-transform: uppercase; color: var(--text-muted);
    background: var(--navy-750); text-align: left;
}
.table-premium tbody td { padding: 13px 16px; border-bottom: 1px solid var(--glass-border); color: var(--text-primary); vertical-align: middle; }
.table-premium tbody tr:last-child td { border-bottom: none; }
.table-premium tbody tr:hover { background: var(--glass-hover); }

@media (max-width: 1200px) {
    .stats-grid-admin { grid-template-columns: repeat(2, 1fr); }
    .stats-grid-admin-row2 { grid-template-columns: repeat(3, 1fr); }
    .dash-cols { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .stats-grid-admin, .stats-grid-admin-row2 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .stats-grid-admin, .stats-grid-admin-row2 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="dash-hero">
    <div>
        <div class="hero-greeting">Hospital Control Center</div>
        <div class="hero-name">Welcome, {{ Str::words(auth()->user()->name, 2, '') }}</div>
        <div class="hero-sub">
            <span class="badge badge-blue" style="font-size:10px;">{{ auth()->user()->role_label }}</span>
            <span style="margin-left:6px;font-size:13px;color:var(--text-muted);">· System Administrator</span>
        </div>
    </div>
    <div style="flex-shrink:0;text-align:center;">
        @php $now = now() @endphp
        <div style="font-size:10px;color:var(--text-muted);letter-spacing:1.2px;text-transform:uppercase;margin-bottom:4px;">Today</div>
        <div style="font-size:24px;font-weight:800;color:var(--text-primary);">{{ $now->format('d M Y') }}</div>
        <div style="font-size:12px;color:var(--text-secondary);">{{ $now->format('l') }}</div>
    </div>
</div>

{{-- Top 4 KPI Tiles --}}
<div class="stats-grid-admin">
    <div class="stat-tile blue">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">Total Patients</div>
        <div class="stat-tile-value">{{ $totalPatients }}</div>
        <div class="stat-tile-sub">Registered patient records</div>
    </div>
    <div class="stat-tile emerald">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">Total Doctors</div>
        <div class="stat-tile-value">{{ $totalDoctors }}</div>
        <div class="stat-tile-sub">Active medical specialists</div>
    </div>
    <div class="stat-tile purple">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">Total Departments</div>
        <div class="stat-tile-value">{{ $totalDepartments }}</div>
        <div class="stat-tile-sub">Hospital departments</div>
    </div>
    <div class="stat-tile orange">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">Today's Appointments</div>
        <div class="stat-tile-value">{{ $todayAppointments }}</div>
        <div class="stat-tile-sub">Scheduled for today</div>
    </div>
</div>

{{-- Row 2 KPI Tiles --}}
<div class="stats-grid-admin-row2">
    <div class="stat-tile yellow">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">Pending Appointments</div>
        <div class="stat-tile-value">{{ $pendingAppointments }}</div>
        <div class="stat-tile-sub">Awaiting confirmation</div>
    </div>
    <div class="stat-tile emerald">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">Completed Appointments</div>
        <div class="stat-tile-value">{{ $completedAppointments }}</div>
        <div class="stat-tile-sub">Successfully treated</div>
    </div>
    <div class="stat-tile cyan">
        <div class="stat-tile-accent"></div>
        <div class="stat-tile-label">System Users</div>
        <div class="stat-tile-value">{{ $totalUsers }}</div>
        <div class="stat-tile-sub">Active admin & staff accounts</div>
    </div>
</div>

{{-- Two Columns: Recent Patients & Recent Appointments --}}
<div class="dash-cols">

    {{-- Recent Patients --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Patients</span>
            <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-sm">View All Patients</a>
        </div>
        <div class="table-wrap">
            @if($recentPatients->count())
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentPatients as $p)
                <tr>
                    <td><strong>{{ $p->full_name }}</strong></td>
                    <td>{{ ucfirst($p->gender ?? '—') }}</td>
                    <td class="text-xs">{{ $p->phone ?? '—' }}</td>
                    <td><span class="badge badge-secondary" style="font-size:11px;">{{ $p->department?->departmentName ?? '—' }}</span></td>
                    <td><span class="badge badge-{{ $p->status_badge }}">{{ ucfirst($p->status) }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state" style="padding:30px;text-align:center;">
                <p class="text-xs text-muted">No recent patients found.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Recent Appointments --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Appointments</span>
            <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm">View All Appointments</a>
        </div>
        <div class="table-wrap">
            @if($recentAppointments->count())
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentAppointments as $app)
                <tr>
                    <td><strong>{{ $app->patient?->full_name ?? '—' }}</strong></td>
                    <td class="text-xs">{{ $app->doctor?->fullName ?? '—' }}</td>
                    <td class="text-xs text-muted">
                        {{ $app->appointmentDate?->format('d M Y') }} {{ $app->formatted_time }}
                    </td>
                    <td><span class="badge badge-{{ $app->status_badge }}">{{ $app->status_label }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state" style="padding:30px;text-align:center;">
                <p class="text-xs text-muted">No recent appointments found.</p>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
