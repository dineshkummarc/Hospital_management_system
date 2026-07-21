@extends('layouts.app')
@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')

@section('content')

<div class="dash-hero" style="background: linear-gradient(135deg, var(--navy-700) 0%, var(--navy-800) 55%, rgba(13,110,253,0.07) 100%); border: 1px solid var(--glass-border); border-radius: var(--radius-xl); padding: 24px; margin-bottom: 24px;">
    <div>
        <div style="font-size: 11px; color: var(--blue-400); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Hospital Portal</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Hello, {{ $user->name }}</div>
        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Role: <span class="badge badge-secondary">{{ $user->role_label }}</span></div>
    </div>
</div>

<div class="row" style="display: flex; gap: 18px; margin-bottom: 24px;">
    <div class="card" style="flex: 1; padding: 20px; background: var(--navy-800); border: 1px solid var(--glass-border); border-radius: var(--radius-lg);">
        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Today's Appointments</div>
        <div style="font-size: 28px; font-weight: 800; color: #0d6efd; margin-top: 6px;">{{ $todayAppointments }}</div>
    </div>
    <div class="card" style="flex: 1; padding: 20px; background: var(--navy-800); border: 1px solid var(--glass-border); border-radius: var(--radius-lg);">
        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Pending Appointments</div>
        <div style="font-size: 28px; font-weight: 800; color: #f59e0b; margin-top: 6px;">{{ $pendingAppointments }}</div>
    </div>
    <div class="card" style="flex: 1; padding: 20px; background: var(--navy-800); border: 1px solid var(--glass-border); border-radius: var(--radius-lg);">
        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Active Patients</div>
        <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 6px;">{{ $totalPatients }}</div>
    </div>
    <div class="card" style="flex: 1; padding: 20px; background: var(--navy-800); border: 1px solid var(--glass-border); border-radius: var(--radius-lg);">
        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Available Doctors</div>
        <div style="font-size: 28px; font-weight: 800; color: #a78bfa; margin-top: 6px;">{{ $totalDoctors }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Upcoming Appointments</span>
        <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div style="overflow-x: auto;">
        @if($upcomingAppointments->count())
        <table class="table-premium" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: var(--navy-750); text-align: left; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                    <th style="padding: 12px 16px;">Patient</th>
                    <th style="padding: 12px 16px;">Doctor</th>
                    <th style="padding: 12px 16px;">Date & Time</th>
                    <th style="padding: 12px 16px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingAppointments as $app)
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 12px 16px;"><strong>{{ $app->patient?->full_name }}</strong></td>
                    <td style="padding: 12px 16px;">{{ $app->doctor?->fullName }}</td>
                    <td style="padding: 12px 16px; color: var(--text-muted);">{{ $app->appointmentDate?->format('d M Y') }} {{ $app->formatted_time }}</td>
                    <td style="padding: 12px 16px;"><span class="badge badge-{{ $app->status_badge }}">{{ $app->status_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding: 30px; text-align: center; color: var(--text-muted);">No upcoming appointments.</div>
        @endif
    </div>
</div>

@endsection
