<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Payment
 *
 * Represents a financial payment in the Hospital Management System.
 * Tracks billing payments, service fees, consultation charges, etc.
 */
class Payment extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'type', 'status', 'mpesa_code',
        'phone', 'mpesa_receipt_number', 'transaction_date',
        'balance_after', 'appointment_id', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function user()       { return $this->belongsTo(User::class); }
    public function appointment(){ return $this->belongsTo(Appointment::class, 'appointment_id'); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function isConfirmed(): bool { return $this->status === 'confirmed'; }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'consultation'  => 'Consultation Fee',
            'service'       => 'Hospital Service',
            'medication'    => 'Medication Charge',
            'procedure'     => 'Medical Procedure',
            'partial'       => 'Partial Payment',
            'deposit'       => 'Admission Deposit',
            default         => ucfirst($this->type),
        };
    }

    public function getStatusBadge(): array
    {
        $map = [
            'confirmed' => ['label' => 'Confirmed', 'class' => 'badge-green'],
            'pending'   => ['label' => 'Pending',   'class' => 'badge-yellow'],
            'failed'    => ['label' => 'Failed',    'class' => 'badge-red'],
            'reversed'  => ['label' => 'Reversed',  'class' => 'badge-gray'],
        ];

        return $map[$this->status] ?? ['label' => 'Unknown', 'class' => 'badge-gray'];
    }
}
