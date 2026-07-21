@extends('layouts.app')
@section('title', 'Hospital Settings')
@section('page-title', 'Hospital Configuration')

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">⚙️ Hospital System Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="hospital_name">Hospital Name</label>
                <input type="text" name="hospital_name" id="hospital_name" class="form-control" value="{{ $settings['hospital_name'] }}" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="contact_email">Contact Email</label>
                    <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ $settings['contact_email'] }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_phone">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ $settings['contact_phone'] }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="sms_sender">SMS Sender ID</label>
                <input type="text" name="sms_sender" id="sms_sender" class="form-control" value="{{ $settings['sms_sender'] }}" required>
            </div>

            <div style="margin-top: 24px; text-align: right;">
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </div>
        </form>
    </div>
</div>
@endsection
