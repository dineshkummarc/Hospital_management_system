<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hospital Management System — Core Tables
 *
 * Creates:
 *   departments   – Hospital departments (Cardiology, Pediatrics, etc.)
 *   doctors       – Medical staff belonging to departments
 *   patients      – Patient registry
 *   appointments  – Appointment bookings linking patients and doctors
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Departments ───────────────────────────────────────────────────────
        Schema::create('departments', function (Blueprint $table) {
            $table->id('departmentID');
            $table->string('departmentName', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // ── Doctors ───────────────────────────────────────────────────────────
        Schema::create('doctors', function (Blueprint $table) {
            $table->id('doctorID');
            $table->string('fullName', 100);
            $table->string('email', 150)->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('specialization', 100)->nullable();
            $table->foreignId('departmentID')
                  ->nullable()
                  ->constrained('departments', 'departmentID')
                  ->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
        });

        // ── Patients ──────────────────────────────────────────────────────────
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patientID');
            $table->string('firstName', 80);
            $table->string('lastName', 80);
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dateOfBirth')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('departmentID')
                  ->nullable()
                  ->constrained('departments', 'departmentID')
                  ->nullOnDelete();
            $table->date('createdDate')->useCurrent();
            $table->enum('status', ['active', 'discharged', 'deceased'])->default('active');
            $table->timestamps();
        });

        // ── Appointments ──────────────────────────────────────────────────────
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointmentID');
            $table->foreignId('patientID')
                  ->constrained('patients', 'patientID')
                  ->cascadeOnDelete();
            $table->foreignId('doctorID')
                  ->constrained('doctors', 'doctorID')
                  ->cascadeOnDelete();
            $table->date('appointmentDate');
            $table->time('appointmentTime')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->date('createdDate')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('departments');
    }
};
