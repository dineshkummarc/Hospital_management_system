<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL Stored Procedures for Hospital Management System.
 *
 * Creates the following stored procedures:
 *  - sp_get_patient_stats         : Aggregate stats for a given patient
 *  - sp_get_department_staff      : Full staff list for a department
 *  - sp_get_upcoming_appointments : Next N scheduled appointments
 *  - sp_record_appointment_result : Update appointment status and outcome
 *  - sp_get_user_activity_report  : Login + role summary per user
 */
return new class extends Migration
{
    /**
     * Run the migrations — create stored procedures.
     * Only created for MySQL/MariaDB; silently skipped on other drivers.
     */
    public function up(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        // Allow multiple statements in one call
        DB::unprepared($this->dropAll());
        DB::unprepared($this->spGetPatientStats());
        DB::unprepared($this->spGetDepartmentStaff());
        DB::unprepared($this->spGetUpcomingAppointments());
        DB::unprepared($this->spRecordAppointmentResult());
        DB::unprepared($this->spGetUserActivityReport());
    }

    /**
     * Reverse the migrations — drop stored procedures.
     */
    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        DB::unprepared($this->dropAll());
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function dropAll(): string
    {
        return "
            DROP PROCEDURE IF EXISTS sp_get_patient_stats;
            DROP PROCEDURE IF EXISTS sp_get_department_staff;
            DROP PROCEDURE IF EXISTS sp_get_upcoming_appointments;
            DROP PROCEDURE IF EXISTS sp_record_appointment_result;
            DROP PROCEDURE IF EXISTS sp_get_user_activity_report;
        ";
    }

    // -------------------------------------------------------------------------
    // Stored Procedures
    // -------------------------------------------------------------------------

    /**
     * sp_get_patient_stats
     * Returns aggregate information about a single patient.
     *
     * IN  p_patient_id  BIGINT  — ID of the patient to retrieve stats for.
     *
     * Result set columns:
     *   patient_id, full_name, gender, blood_group, age, phone, email, created_at
     */
    private function spGetPatientStats(): string
    {
        return "
CREATE PROCEDURE sp_get_patient_stats(IN p_patient_id BIGINT)
BEGIN
    SELECT
        p.id                                       AS patient_id,
        p.name                                     AS full_name,
        p.gender,
        p.blood_group,
        TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
        p.phone,
        p.email,
        p.created_at
    FROM patients p
    WHERE p.id = p_patient_id;
END
";
    }

    /**
     * sp_get_department_staff
     * Returns the full staff list assigned to a given department.
     *
     * IN  p_department_id  BIGINT  — ID of the department.
     *
     * Result set columns:
     *   staff_id, name, role, specialization, phone, email, joined_at
     */
    private function spGetDepartmentStaff(): string
    {
        return "
CREATE PROCEDURE sp_get_department_staff(IN p_department_id BIGINT)
BEGIN
    SELECT
        d.id          AS staff_id,
        d.name,
        d.role,
        d.specialization,
        d.phone,
        d.email,
        d.created_at  AS joined_at
    FROM doctors d
    WHERE d.department_id = p_department_id
    ORDER BY d.name;
END
";
    }

    /**
     * sp_get_upcoming_appointments
     * Returns the next N appointments sorted by date/time ascending.
     *
     * IN  p_limit  INT  — Maximum number of appointments to return (default 10).
     *
     * Result set columns:
     *   appointment_id, patient_name, doctor_name, department, appointment_date, appointment_time, status
     */
    private function spGetUpcomingAppointments(): string
    {
        return "
CREATE PROCEDURE sp_get_upcoming_appointments(IN p_limit INT)
BEGIN
    DECLARE v_limit INT;
    SET v_limit = IFNULL(p_limit, 10);

    SELECT
        a.id                AS appointment_id,
        p.name              AS patient_name,
        d.name              AS doctor_name,
        dep.name            AS department,
        a.appointment_date,
        a.appointment_time,
        a.status
    FROM appointments a
    LEFT JOIN patients    p   ON p.id   = a.patient_id
    LEFT JOIN doctors     d   ON d.id   = a.doctor_id
    LEFT JOIN departments dep ON dep.id = a.department_id
    WHERE a.appointment_date >= CURDATE()
      AND a.status = 'scheduled'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT v_limit;
END
";
    }

    /**
     * sp_record_appointment_result
     * Updates the status and notes of an appointment after it is completed.
     *
     * IN  p_appointment_id  BIGINT   — ID of the appointment to update.
     * IN  p_status          VARCHAR  — New status (e.g. 'completed', 'cancelled').
     * IN  p_notes           TEXT     — Doctor notes / diagnosis summary.
     * OUT p_success         TINYINT  — 1 if updated, 0 if not found.
     */
    private function spRecordAppointmentResult(): string
    {
        return "
CREATE PROCEDURE sp_record_appointment_result(
    IN  p_appointment_id  BIGINT,
    IN  p_status          VARCHAR(50),
    IN  p_notes           TEXT,
    OUT p_success         TINYINT
)
BEGIN
    DECLARE v_count INT DEFAULT 0;

    SELECT COUNT(*) INTO v_count FROM appointments WHERE id = p_appointment_id;

    IF v_count > 0 THEN
        UPDATE appointments
        SET status     = p_status,
            notes      = p_notes,
            updated_at = NOW()
        WHERE id = p_appointment_id;
        SET p_success = 1;
    ELSE
        SET p_success = 0;
    END IF;
END
";
    }

    /**
     * sp_get_user_activity_report
     * Summarises user account details alongside their assigned role.
     *
     * No parameters.
     *
     * Result set columns:
     *   user_id, name, email, role, account_status, created_at
     */
    private function spGetUserActivityReport(): string
    {
        return "
CREATE PROCEDURE sp_get_user_activity_report()
BEGIN
    SELECT
        u.id                AS user_id,
        u.name,
        u.email,
        u.role,
        IF(u.is_active, 'active', 'inactive') AS account_status,
        u.created_at
    FROM users u
    ORDER BY u.created_at DESC;
END
";
    }
};
