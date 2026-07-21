<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hospital Management System — Stored Procedures
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        DB::unprepared($this->dropAll());

        // Patients
        DB::unprepared($this->spCheckPatient());
        DB::unprepared($this->spSavePatient());
        DB::unprepared($this->spFilterPatients());
        DB::unprepared($this->spGetPatientDetails());
        DB::unprepared($this->spDeletePatient());

        // Doctors
        DB::unprepared($this->spCheckDoctor());
        DB::unprepared($this->spSaveDoctor());
        DB::unprepared($this->spFilterDoctors());
        DB::unprepared($this->spGetDoctorDetails());
        DB::unprepared($this->spDeleteDoctor());

        // Departments
        DB::unprepared($this->spCheckDepartment());
        DB::unprepared($this->spSaveDepartment());
        DB::unprepared($this->spFilterDepartments());
        DB::unprepared($this->spGetDepartmentDetails());
        DB::unprepared($this->spDeleteDepartment());

        // Appointments
        DB::unprepared($this->spCheckAppointment());
        DB::unprepared($this->spSaveAppointment());
        DB::unprepared($this->spFilterAppointments());
        DB::unprepared($this->spGetAppointmentDetails());
        DB::unprepared($this->spDeleteAppointment());

        // Reporting
        DB::unprepared($this->spGetUserActivityReport());
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }
        DB::unprepared($this->dropAll());
    }

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function dropAll(): string
    {
        return "
            DROP PROCEDURE IF EXISTS SP_check_patient;
            DROP PROCEDURE IF EXISTS SP_save_patient;
            DROP PROCEDURE IF EXISTS SP_filter_patients;
            DROP PROCEDURE IF EXISTS SP_get_patient_details;
            DROP PROCEDURE IF EXISTS SP_delete_patient;

            DROP PROCEDURE IF EXISTS SP_check_doctor;
            DROP PROCEDURE IF EXISTS SP_save_doctor;
            DROP PROCEDURE IF EXISTS SP_filter_doctors;
            DROP PROCEDURE IF EXISTS SP_get_doctor_details;
            DROP PROCEDURE IF EXISTS SP_delete_doctor;

            DROP PROCEDURE IF EXISTS SP_check_department;
            DROP PROCEDURE IF EXISTS SP_save_department;
            DROP PROCEDURE IF EXISTS SP_filter_departments;
            DROP PROCEDURE IF EXISTS SP_get_department_details;
            DROP PROCEDURE IF EXISTS SP_delete_department;

            DROP PROCEDURE IF EXISTS SP_check_appointment;
            DROP PROCEDURE IF EXISTS SP_save_appointment;
            DROP PROCEDURE IF EXISTS SP_filter_appointments;
            DROP PROCEDURE IF EXISTS SP_get_appointment_details;
            DROP PROCEDURE IF EXISTS SP_delete_appointment;

            DROP PROCEDURE IF EXISTS sp_get_user_activity_report;
        ";
    }

    private function spCheckPatient(): string
    {
        return "
CREATE PROCEDURE SP_check_patient(
    IN  p_phone    VARCHAR(20),
    IN  p_email    VARCHAR(150),
    OUT p_exists   TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_exists
    FROM patients
    WHERE phone = p_phone
       OR (p_email IS NOT NULL AND email = p_email);
END
";
    }

    private function spSavePatient(): string
    {
        return "
CREATE PROCEDURE SP_save_patient(
    IN  p_patientID     BIGINT,
    IN  p_firstName     VARCHAR(80),
    IN  p_lastName      VARCHAR(80),
    IN  p_gender        VARCHAR(10),
    IN  p_dateOfBirth   DATE,
    IN  p_phone         VARCHAR(20),
    IN  p_email         VARCHAR(150),
    IN  p_address       TEXT,
    IN  p_departmentID  BIGINT,
    IN  p_status        VARCHAR(20),
    OUT p_result_id     BIGINT
)
BEGIN
    IF p_patientID = 0 THEN
        INSERT INTO patients
            (firstName, lastName, gender, dateOfBirth, phone, email, address, departmentID, status, createdDate, created_at, updated_at)
        VALUES
            (p_firstName, p_lastName, p_gender, p_dateOfBirth, p_phone, p_email, p_address, p_departmentID, p_status, CURDATE(), NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE patients
        SET firstName    = p_firstName,
            lastName     = p_lastName,
            gender       = p_gender,
            dateOfBirth  = p_dateOfBirth,
            phone        = p_phone,
            email        = p_email,
            address      = p_address,
            departmentID = p_departmentID,
            status       = p_status,
            updated_at   = NOW()
        WHERE patientID  = p_patientID;
        SET p_result_id = p_patientID;
    END IF;
END
";
    }

    private function spFilterPatients(): string
    {
        return "
CREATE PROCEDURE SP_filter_patients(
    IN p_search       VARCHAR(100),
    IN p_status       VARCHAR(20),
    IN p_departmentID BIGINT,
    IN p_limit        INT,
    IN p_offset       INT
)
BEGIN
    DECLARE v_limit  INT DEFAULT 20;
    DECLARE v_offset INT DEFAULT 0;
    SET v_limit  = IFNULL(p_limit, 20);
    SET v_offset = IFNULL(p_offset, 0);

    SELECT
        pt.patientID,
        pt.firstName,
        pt.lastName,
        CONCAT(pt.firstName, ' ', pt.lastName) AS fullName,
        pt.gender,
        pt.dateOfBirth,
        TIMESTAMPDIFF(YEAR, pt.dateOfBirth, CURDATE()) AS age,
        pt.phone,
        pt.email,
        pt.status,
        pt.createdDate,
        d.departmentName
    FROM patients pt
    LEFT JOIN departments d ON d.departmentID = pt.departmentID
    WHERE (p_search IS NULL OR p_search = ''
           OR pt.firstName LIKE CONCAT('%', p_search, '%')
           OR pt.lastName  LIKE CONCAT('%', p_search, '%')
           OR pt.phone     LIKE CONCAT('%', p_search, '%')
           OR pt.email     LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR pt.status = p_status)
      AND (p_departmentID IS NULL OR p_departmentID = 0 OR pt.departmentID = p_departmentID)
    ORDER BY pt.createdDate DESC, pt.patientID DESC
    LIMIT v_limit OFFSET v_offset;
END
";
    }

    private function spGetPatientDetails(): string
    {
        return "
CREATE PROCEDURE SP_get_patient_details(IN p_patientID BIGINT)
BEGIN
    SELECT
        pt.patientID,
        pt.firstName,
        pt.lastName,
        CONCAT(pt.firstName, ' ', pt.lastName) AS fullName,
        pt.gender,
        pt.dateOfBirth,
        TIMESTAMPDIFF(YEAR, pt.dateOfBirth, CURDATE()) AS age,
        pt.phone,
        pt.email,
        pt.address,
        pt.status,
        pt.createdDate,
        d.departmentID,
        d.departmentName
    FROM patients pt
    LEFT JOIN departments d ON d.departmentID = pt.departmentID
    WHERE pt.patientID = p_patientID;
END
";
    }

    private function spDeletePatient(): string
    {
        return "
CREATE PROCEDURE SP_delete_patient(
    IN  p_patientID BIGINT,
    OUT p_affected  INT
)
BEGIN
    DELETE FROM patients WHERE patientID = p_patientID;
    SET p_affected = ROW_COUNT();
END
";
    }

    private function spCheckDoctor(): string
    {
        return "
CREATE PROCEDURE SP_check_doctor(
    IN  p_email   VARCHAR(150),
    IN  p_phone   VARCHAR(20),
    OUT p_exists  TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_exists
    FROM doctors
    WHERE (p_email IS NOT NULL AND email = p_email)
       OR (p_phone IS NOT NULL AND phone = p_phone);
END
";
    }

    private function spSaveDoctor(): string
    {
        return "
CREATE PROCEDURE SP_save_doctor(
    IN  p_doctorID       BIGINT,
    IN  p_fullName       VARCHAR(100),
    IN  p_email          VARCHAR(150),
    IN  p_phone          VARCHAR(20),
    IN  p_specialization VARCHAR(100),
    IN  p_departmentID   BIGINT,
    IN  p_status         VARCHAR(20),
    OUT p_result_id      BIGINT
)
BEGIN
    IF p_doctorID = 0 THEN
        INSERT INTO doctors
            (fullName, email, phone, specialization, departmentID, status, created_at, updated_at)
        VALUES
            (p_fullName, p_email, p_phone, p_specialization, p_departmentID, p_status, NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE doctors
        SET fullName       = p_fullName,
            email          = p_email,
            phone          = p_phone,
            specialization = p_specialization,
            departmentID   = p_departmentID,
            status         = p_status,
            updated_at     = NOW()
        WHERE doctorID = p_doctorID;
        SET p_result_id = p_doctorID;
    END IF;
END
";
    }

    private function spFilterDoctors(): string
    {
        return "
CREATE PROCEDURE SP_filter_doctors(
    IN p_search       VARCHAR(100),
    IN p_status       VARCHAR(20),
    IN p_departmentID BIGINT,
    IN p_limit        INT,
    IN p_offset       INT
)
BEGIN
    DECLARE v_limit  INT DEFAULT 20;
    DECLARE v_offset INT DEFAULT 0;
    SET v_limit  = IFNULL(p_limit, 20);
    SET v_offset = IFNULL(p_offset, 0);

    SELECT
        dr.doctorID,
        dr.fullName,
        dr.email,
        dr.phone,
        dr.specialization,
        dr.status,
        d.departmentID,
        d.departmentName
    FROM doctors dr
    LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE (p_search IS NULL OR p_search = ''
           OR dr.fullName        LIKE CONCAT('%', p_search, '%')
           OR dr.email           LIKE CONCAT('%', p_search, '%')
           OR dr.specialization  LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR dr.status = p_status)
      AND (p_departmentID IS NULL OR p_departmentID = 0 OR dr.departmentID = p_departmentID)
    ORDER BY dr.fullName ASC
    LIMIT v_limit OFFSET v_offset;
END
";
    }

    private function spGetDoctorDetails(): string
    {
        return "
CREATE PROCEDURE SP_get_doctor_details(IN p_doctorID BIGINT)
BEGIN
    SELECT
        dr.doctorID,
        dr.fullName,
        dr.email,
        dr.phone,
        dr.specialization,
        dr.status,
        d.departmentID,
        d.departmentName,
        (SELECT COUNT(*) FROM appointments a WHERE a.doctorID = dr.doctorID) AS totalAppointments,
        (SELECT COUNT(*) FROM appointments a WHERE a.doctorID = dr.doctorID AND a.status = 'pending') AS pendingAppointments
    FROM doctors dr
    LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE dr.doctorID = p_doctorID;
END
";
    }

    private function spDeleteDoctor(): string
    {
        return "
CREATE PROCEDURE SP_delete_doctor(
    IN  p_doctorID BIGINT,
    OUT p_affected INT
)
BEGIN
    DELETE FROM doctors WHERE doctorID = p_doctorID;
    SET p_affected = ROW_COUNT();
END
";
    }

    private function spCheckDepartment(): string
    {
        return "
CREATE PROCEDURE SP_check_department(
    IN  p_departmentName VARCHAR(100),
    IN  p_excludeID      BIGINT,
    OUT p_exists         TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_exists
    FROM departments
    WHERE departmentName = p_departmentName
      AND (p_excludeID IS NULL OR p_excludeID = 0 OR departmentID <> p_excludeID);
END
";
    }

    private function spSaveDepartment(): string
    {
        return "
CREATE PROCEDURE SP_save_department(
    IN  p_departmentID   BIGINT,
    IN  p_departmentName VARCHAR(100),
    IN  p_description    TEXT,
    IN  p_status         VARCHAR(20),
    OUT p_result_id      BIGINT
)
BEGIN
    IF p_departmentID = 0 THEN
        INSERT INTO departments (departmentName, description, status, created_at, updated_at)
        VALUES (p_departmentName, p_description, p_status, NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE departments
        SET departmentName = p_departmentName,
            description    = p_description,
            status         = p_status,
            updated_at     = NOW()
        WHERE departmentID = p_departmentID;
        SET p_result_id = p_departmentID;
    END IF;
END
";
    }

    private function spFilterDepartments(): string
    {
        return "
CREATE PROCEDURE SP_filter_departments(
    IN p_search VARCHAR(100),
    IN p_status VARCHAR(20)
)
BEGIN
    SELECT
        d.departmentID,
        d.departmentName,
        d.description,
        d.status,
        (SELECT COUNT(*) FROM doctors   dr WHERE dr.departmentID = d.departmentID AND dr.status = 'active') AS doctorCount,
        (SELECT COUNT(*) FROM patients  pt WHERE pt.departmentID = d.departmentID AND pt.status = 'active') AS patientCount
    FROM departments d
    WHERE (p_search IS NULL OR p_search = ''
           OR d.departmentName LIKE CONCAT('%', p_search, '%')
           OR d.description    LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR d.status = p_status)
    ORDER BY d.departmentName ASC;
END
";
    }

    private function spGetDepartmentDetails(): string
    {
        return "
CREATE PROCEDURE SP_get_department_details(IN p_departmentID BIGINT)
BEGIN
    SELECT
        d.departmentID,
        d.departmentName,
        d.description,
        d.status,
        (SELECT COUNT(*) FROM doctors   dr WHERE dr.departmentID = d.departmentID) AS totalDoctors,
        (SELECT COUNT(*) FROM patients  pt WHERE pt.departmentID = d.departmentID) AS totalPatients
    FROM departments d
    WHERE d.departmentID = p_departmentID;
END
";
    }

    private function spDeleteDepartment(): string
    {
        return "
CREATE PROCEDURE SP_delete_department(
    IN  p_departmentID BIGINT,
    OUT p_affected     INT
)
BEGIN
    DELETE FROM departments WHERE departmentID = p_departmentID;
    SET p_affected = ROW_COUNT();
END
";
    }

    private function spCheckAppointment(): string
    {
        return "
CREATE PROCEDURE SP_check_appointment(
    IN  p_patientID       BIGINT,
    IN  p_doctorID        BIGINT,
    IN  p_appointmentDate DATE,
    IN  p_appointmentTime TIME,
    IN  p_excludeID       BIGINT,
    OUT p_exists          TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_exists
    FROM appointments
    WHERE patientID       = p_patientID
      AND doctorID        = p_doctorID
      AND appointmentDate = p_appointmentDate
      AND appointmentTime = p_appointmentTime
      AND status NOT IN ('cancelled')
      AND (p_excludeID IS NULL OR p_excludeID = 0 OR appointmentID <> p_excludeID);
END
";
    }

    private function spSaveAppointment(): string
    {
        return "
CREATE PROCEDURE SP_save_appointment(
    IN  p_appointmentID   BIGINT,
    IN  p_patientID       BIGINT,
    IN  p_doctorID        BIGINT,
    IN  p_appointmentDate DATE,
    IN  p_appointmentTime TIME,
    IN  p_reason          TEXT,
    IN  p_status          VARCHAR(20),
    OUT p_result_id       BIGINT
)
BEGIN
    IF p_appointmentID = 0 THEN
        INSERT INTO appointments
            (patientID, doctorID, appointmentDate, appointmentTime, reason, status, createdDate, created_at, updated_at)
        VALUES
            (p_patientID, p_doctorID, p_appointmentDate, p_appointmentTime, p_reason, p_status, CURDATE(), NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE appointments
        SET patientID       = p_patientID,
            doctorID        = p_doctorID,
            appointmentDate = p_appointmentDate,
            appointmentTime = p_appointmentTime,
            reason          = p_reason,
            status          = p_status,
            updated_at      = NOW()
        WHERE appointmentID = p_appointmentID;
        SET p_result_id = p_appointmentID;
    END IF;
END
";
    }

    private function spFilterAppointments(): string
    {
        return "
CREATE PROCEDURE SP_filter_appointments(
    IN p_search       VARCHAR(100),
    IN p_status       VARCHAR(20),
    IN p_doctorID     BIGINT,
    IN p_departmentID BIGINT,
    IN p_date_from    DATE,
    IN p_date_to      DATE,
    IN p_limit        INT,
    IN p_offset       INT
)
BEGIN
    DECLARE v_limit  INT DEFAULT 20;
    DECLARE v_offset INT DEFAULT 0;
    SET v_limit  = IFNULL(p_limit, 20);
    SET v_offset = IFNULL(p_offset, 0);

    SELECT
        a.appointmentID,
        a.appointmentDate,
        a.appointmentTime,
        a.reason,
        a.status,
        a.createdDate,
        CONCAT(pt.firstName, ' ', pt.lastName) AS patientName,
        pt.phone                               AS patientPhone,
        dr.fullName                            AS doctorName,
        dr.specialization,
        d.departmentName
    FROM appointments a
    JOIN patients    pt ON pt.patientID  = a.patientID
    JOIN doctors     dr ON dr.doctorID   = a.doctorID
    LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE (p_search IS NULL OR p_search = ''
           OR pt.firstName  LIKE CONCAT('%', p_search, '%')
           OR pt.lastName   LIKE CONCAT('%', p_search, '%')
           OR dr.fullName   LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR a.status = p_status)
      AND (p_doctorID IS NULL OR p_doctorID = 0 OR a.doctorID = p_doctorID)
      AND (p_departmentID IS NULL OR p_departmentID = 0 OR dr.departmentID = p_departmentID)
      AND (p_date_from IS NULL OR a.appointmentDate >= p_date_from)
      AND (p_date_to   IS NULL OR a.appointmentDate <= p_date_to)
    ORDER BY a.appointmentDate DESC, a.appointmentTime DESC
    LIMIT v_limit OFFSET v_offset;
END
";
    }

    private function spGetAppointmentDetails(): string
    {
        return "
CREATE PROCEDURE SP_get_appointment_details(IN p_appointmentID BIGINT)
BEGIN
    SELECT
        a.appointmentID,
        a.appointmentDate,
        a.appointmentTime,
        a.reason,
        a.status,
        a.createdDate,
        pt.patientID,
        CONCAT(pt.firstName, ' ', pt.lastName) AS patientName,
        pt.phone      AS patientPhone,
        pt.email      AS patientEmail,
        dr.doctorID,
        dr.fullName   AS doctorName,
        dr.specialization,
        d.departmentID,
        d.departmentName
    FROM appointments a
    JOIN patients    pt ON pt.patientID  = a.patientID
    JOIN doctors     dr ON dr.doctorID   = a.doctorID
    LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE a.appointmentID = p_appointmentID;
END
";
    }

    private function spDeleteAppointment(): string
    {
        return "
CREATE PROCEDURE SP_delete_appointment(
    IN  p_appointmentID BIGINT,
    OUT p_affected      INT
)
BEGIN
    DELETE FROM appointments WHERE appointmentID = p_appointmentID;
    SET p_affected = ROW_COUNT();
END
";
    }

    private function spGetUserActivityReport(): string
    {
        return "
CREATE PROCEDURE sp_get_user_activity_report()
BEGIN
    SELECT
        u.id         AS user_id,
        u.name,
        u.email,
        u.role,
        u.is_active  AS account_status,
        u.created_at
    FROM users u
    ORDER BY u.created_at DESC;
END
";
    }
};
