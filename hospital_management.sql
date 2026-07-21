-- Hospital Management System — Database Schema & Stored Procedures Dump

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Table structure for departments
-- --------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `departmentID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `departmentName` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`departmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for doctors
-- --------------------------------------------------------
DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
  `doctorID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullName` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `departmentID` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`doctorID`),
  UNIQUE KEY `doctors_email_unique` (`email`),
  UNIQUE KEY `doctors_phone_unique` (`phone`),
  KEY `doctors_departmentid_foreign` (`departmentID`),
  CONSTRAINT `doctors_departmentid_foreign` FOREIGN KEY (`departmentID`) REFERENCES `departments` (`departmentID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for patients
-- --------------------------------------------------------
DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `patientID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstName` varchar(80) NOT NULL,
  `lastName` varchar(80) NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `departmentID` bigint(20) UNSIGNED DEFAULT NULL,
  `createdDate` date NOT NULL DEFAULT curdate(),
  `status` enum('active','discharged','deceased') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`patientID`),
  KEY `patients_departmentid_foreign` (`departmentID`),
  CONSTRAINT `patients_departmentid_foreign` FOREIGN KEY (`departmentID`) REFERENCES `departments` (`departmentID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for appointments
-- --------------------------------------------------------
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointmentID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `patientID` bigint(20) UNSIGNED NOT NULL,
  `doctorID` bigint(20) UNSIGNED NOT NULL,
  `appointmentDate` date NOT NULL,
  `appointmentTime` time DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `createdDate` date NOT NULL DEFAULT curdate(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`appointmentID`),
  KEY `appointments_patientid_foreign` (`patientID`),
  KEY `appointments_doctorid_foreign` (`doctorID`),
  CONSTRAINT `appointments_doctorid_foreign` FOREIGN KEY (`doctorID`) REFERENCES `doctors` (`doctorID`) ON DELETE CASCADE,
  CONSTRAINT `appointments_patientid_foreign` FOREIGN KEY (`patientID`) REFERENCES `patients` (`patientID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for users
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Stored Procedures (20 total)
-- --------------------------------------------------------

DELIMITER $$

-- Patients SPs
DROP PROCEDURE IF EXISTS SP_check_patient$$
CREATE PROCEDURE SP_check_patient(IN p_phone VARCHAR(20), IN p_email VARCHAR(150), OUT p_exists TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_exists FROM patients WHERE phone = p_phone OR (p_email IS NOT NULL AND email = p_email);
END$$

DROP PROCEDURE IF EXISTS SP_save_patient$$
CREATE PROCEDURE SP_save_patient(IN p_patientID BIGINT, IN p_firstName VARCHAR(80), IN p_lastName VARCHAR(80), IN p_gender VARCHAR(10), IN p_dateOfBirth DATE, IN p_phone VARCHAR(20), IN p_email VARCHAR(150), IN p_address TEXT, IN p_departmentID BIGINT, IN p_status VARCHAR(20), OUT p_result_id BIGINT)
BEGIN
    IF p_patientID = 0 THEN
        INSERT INTO patients (firstName, lastName, gender, dateOfBirth, phone, email, address, departmentID, status, createdDate, created_at, updated_at)
        VALUES (p_firstName, p_lastName, p_gender, p_dateOfBirth, p_phone, p_email, p_address, p_departmentID, p_status, CURDATE(), NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE patients SET firstName=p_firstName, lastName=p_lastName, gender=p_gender, dateOfBirth=p_dateOfBirth, phone=p_phone, email=p_email, address=p_address, departmentID=p_departmentID, status=p_status, updated_at=NOW() WHERE patientID=p_patientID;
        SET p_result_id = p_patientID;
    END IF;
END$$

DROP PROCEDURE IF EXISTS SP_filter_patients$$
CREATE PROCEDURE SP_filter_patients(IN p_search VARCHAR(100), IN p_status VARCHAR(20), IN p_departmentID BIGINT, IN p_limit INT, IN p_offset INT)
BEGIN
    SELECT pt.patientID, pt.firstName, pt.lastName, CONCAT(pt.firstName, ' ', pt.lastName) AS fullName, pt.gender, pt.dateOfBirth, TIMESTAMPDIFF(YEAR, pt.dateOfBirth, CURDATE()) AS age, pt.phone, pt.email, pt.status, pt.createdDate, d.departmentName
    FROM patients pt LEFT JOIN departments d ON d.departmentID = pt.departmentID
    WHERE (p_search IS NULL OR p_search = '' OR pt.firstName LIKE CONCAT('%', p_search, '%') OR pt.lastName LIKE CONCAT('%', p_search, '%') OR pt.phone LIKE CONCAT('%', p_search, '%') OR pt.email LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR pt.status = p_status)
      AND (p_departmentID IS NULL OR p_departmentID = 0 OR pt.departmentID = p_departmentID)
    ORDER BY pt.createdDate DESC, pt.patientID DESC LIMIT IFNULL(p_limit, 20) OFFSET IFNULL(p_offset, 0);
END$$

DROP PROCEDURE IF EXISTS SP_get_patient_details$$
CREATE PROCEDURE SP_get_patient_details(IN p_patientID BIGINT)
BEGIN
    SELECT pt.patientID, pt.firstName, pt.lastName, CONCAT(pt.firstName, ' ', pt.lastName) AS fullName, pt.gender, pt.dateOfBirth, TIMESTAMPDIFF(YEAR, pt.dateOfBirth, CURDATE()) AS age, pt.phone, pt.email, pt.address, pt.status, pt.createdDate, d.departmentID, d.departmentName
    FROM patients pt LEFT JOIN departments d ON d.departmentID = pt.departmentID
    WHERE pt.patientID = p_patientID;
END$$

DROP PROCEDURE IF EXISTS SP_delete_patient$$
CREATE PROCEDURE SP_delete_patient(IN p_patientID BIGINT, OUT p_affected INT)
BEGIN
    DELETE FROM patients WHERE patientID = p_patientID;
    SET p_affected = ROW_COUNT();
END$$

-- Doctors SPs
DROP PROCEDURE IF EXISTS SP_check_doctor$$
CREATE PROCEDURE SP_check_doctor(IN p_email VARCHAR(150), IN p_phone VARCHAR(20), OUT p_exists TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_exists FROM doctors WHERE (p_email IS NOT NULL AND email = p_email) OR (p_phone IS NOT NULL AND phone = p_phone);
END$$

DROP PROCEDURE IF EXISTS SP_save_doctor$$
CREATE PROCEDURE SP_save_doctor(IN p_doctorID BIGINT, IN p_fullName VARCHAR(100), IN p_email VARCHAR(150), IN p_phone VARCHAR(20), IN p_specialization VARCHAR(100), IN p_departmentID BIGINT, IN p_status VARCHAR(20), OUT p_result_id BIGINT)
BEGIN
    IF p_doctorID = 0 THEN
        INSERT INTO doctors (fullName, email, phone, specialization, departmentID, status, created_at, updated_at)
        VALUES (p_fullName, p_email, p_phone, p_specialization, p_departmentID, p_status, NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE doctors SET fullName=p_fullName, email=p_email, phone=p_phone, specialization=p_specialization, departmentID=p_departmentID, status=p_status, updated_at=NOW() WHERE doctorID=p_doctorID;
        SET p_result_id = p_doctorID;
    END IF;
END$$

DROP PROCEDURE IF EXISTS SP_filter_doctors$$
CREATE PROCEDURE SP_filter_doctors(IN p_search VARCHAR(100), IN p_status VARCHAR(20), IN p_departmentID BIGINT, IN p_limit INT, IN p_offset INT)
BEGIN
    SELECT dr.doctorID, dr.fullName, dr.email, dr.phone, dr.specialization, dr.status, d.departmentID, d.departmentName
    FROM doctors dr LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE (p_search IS NULL OR p_search = '' OR dr.fullName LIKE CONCAT('%', p_search, '%') OR dr.email LIKE CONCAT('%', p_search, '%') OR dr.specialization LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR dr.status = p_status)
      AND (p_departmentID IS NULL OR p_departmentID = 0 OR dr.departmentID = p_departmentID)
    ORDER BY dr.fullName ASC LIMIT IFNULL(p_limit, 20) OFFSET IFNULL(p_offset, 0);
END$$

DROP PROCEDURE IF EXISTS SP_get_doctor_details$$
CREATE PROCEDURE SP_get_doctor_details(IN p_doctorID BIGINT)
BEGIN
    SELECT dr.doctorID, dr.fullName, dr.email, dr.phone, dr.specialization, dr.status, d.departmentID, d.departmentName,
        (SELECT COUNT(*) FROM appointments a WHERE a.doctorID = dr.doctorID) AS totalAppointments,
        (SELECT COUNT(*) FROM appointments a WHERE a.doctorID = dr.doctorID AND a.status = 'pending') AS pendingAppointments
    FROM doctors dr LEFT JOIN departments d ON d.departmentID = dr.departmentID WHERE dr.doctorID = p_doctorID;
END$$

DROP PROCEDURE IF EXISTS SP_delete_doctor$$
CREATE PROCEDURE SP_delete_doctor(IN p_doctorID BIGINT, OUT p_affected INT)
BEGIN
    DELETE FROM doctors WHERE doctorID = p_doctorID;
    SET p_affected = ROW_COUNT();
END$$

-- Departments SPs
DROP PROCEDURE IF EXISTS SP_check_department$$
CREATE PROCEDURE SP_check_department(IN p_departmentName VARCHAR(100), IN p_excludeID BIGINT, OUT p_exists TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_exists FROM departments WHERE departmentName = p_departmentName AND (p_excludeID IS NULL OR p_excludeID = 0 OR departmentID <> p_excludeID);
END$$

DROP PROCEDURE IF EXISTS SP_save_department$$
CREATE PROCEDURE SP_save_department(IN p_departmentID BIGINT, IN p_departmentName VARCHAR(100), IN p_description TEXT, IN p_status VARCHAR(20), OUT p_result_id BIGINT)
BEGIN
    IF p_departmentID = 0 THEN
        INSERT INTO departments (departmentName, description, status, created_at, updated_at) VALUES (p_departmentName, p_description, p_status, NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE departments SET departmentName=p_departmentName, description=p_description, status=p_status, updated_at=NOW() WHERE departmentID=p_departmentID;
        SET p_result_id = p_departmentID;
    END IF;
END$$

DROP PROCEDURE IF EXISTS SP_filter_departments$$
CREATE PROCEDURE SP_filter_departments(IN p_search VARCHAR(100), IN p_status VARCHAR(20))
BEGIN
    SELECT d.departmentID, d.departmentName, d.description, d.status,
        (SELECT COUNT(*) FROM doctors dr WHERE dr.departmentID = d.departmentID AND dr.status = 'active') AS doctorCount,
        (SELECT COUNT(*) FROM patients pt WHERE pt.departmentID = d.departmentID AND pt.status = 'active') AS patientCount
    FROM departments d
    WHERE (p_search IS NULL OR p_search = '' OR d.departmentName LIKE CONCAT('%', p_search, '%') OR d.description LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR d.status = p_status)
    ORDER BY d.departmentName ASC;
END$$

DROP PROCEDURE IF EXISTS SP_get_department_details$$
CREATE PROCEDURE SP_get_department_details(IN p_departmentID BIGINT)
BEGIN
    SELECT d.departmentID, d.departmentName, d.description, d.status,
        (SELECT COUNT(*) FROM doctors dr WHERE dr.departmentID = d.departmentID) AS totalDoctors,
        (SELECT COUNT(*) FROM patients pt WHERE pt.departmentID = d.departmentID) AS totalPatients
    FROM departments d WHERE d.departmentID = p_departmentID;
END$$

DROP PROCEDURE IF EXISTS SP_delete_department$$
CREATE PROCEDURE SP_delete_department(IN p_departmentID BIGINT, OUT p_affected INT)
BEGIN
    DELETE FROM departments WHERE departmentID = p_departmentID;
    SET p_affected = ROW_COUNT();
END$$

-- Appointments SPs
DROP PROCEDURE IF EXISTS SP_check_appointment$$
CREATE PROCEDURE SP_check_appointment(IN p_patientID BIGINT, IN p_doctorID BIGINT, IN p_appointmentDate DATE, IN p_appointmentTime TIME, IN p_excludeID BIGINT, OUT p_exists TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_exists FROM appointments WHERE patientID = p_patientID AND doctorID = p_doctorID AND appointmentDate = p_appointmentDate AND appointmentTime = p_appointmentTime AND status NOT IN ('cancelled') AND (p_excludeID IS NULL OR p_excludeID = 0 OR appointmentID <> p_excludeID);
END$$

DROP PROCEDURE IF EXISTS SP_save_appointment$$
CREATE PROCEDURE SP_save_appointment(IN p_appointmentID BIGINT, IN p_patientID BIGINT, IN p_doctorID BIGINT, IN p_appointmentDate DATE, IN p_appointmentTime TIME, IN p_reason TEXT, IN p_status VARCHAR(20), OUT p_result_id BIGINT)
BEGIN
    IF p_appointmentID = 0 THEN
        INSERT INTO appointments (patientID, doctorID, appointmentDate, appointmentTime, reason, status, createdDate, created_at, updated_at)
        VALUES (p_patientID, p_doctorID, p_appointmentDate, p_appointmentTime, p_reason, p_status, CURDATE(), NOW(), NOW());
        SET p_result_id = LAST_INSERT_ID();
    ELSE
        UPDATE appointments SET patientID=p_patientID, doctorID=p_doctorID, appointmentDate=p_appointmentDate, appointmentTime=p_appointmentTime, reason=p_reason, status=p_status, updated_at=NOW() WHERE appointmentID=p_appointmentID;
        SET p_result_id = p_appointmentID;
    END IF;
END$$

DROP PROCEDURE IF EXISTS SP_filter_appointments$$
CREATE PROCEDURE SP_filter_appointments(IN p_search VARCHAR(100), IN p_status VARCHAR(20), IN p_doctorID BIGINT, IN p_departmentID BIGINT, IN p_date_from DATE, IN p_date_to DATE, IN p_limit INT, IN p_offset INT)
BEGIN
    SELECT a.appointmentID, a.appointmentDate, a.appointmentTime, a.reason, a.status, a.createdDate,
        CONCAT(pt.firstName, ' ', pt.lastName) AS patientName, pt.phone AS patientPhone, dr.fullName AS doctorName, dr.specialization, d.departmentName
    FROM appointments a
    JOIN patients pt ON pt.patientID = a.patientID
    JOIN doctors dr ON dr.doctorID = a.doctorID
    LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE (p_search IS NULL OR p_search = '' OR pt.firstName LIKE CONCAT('%', p_search, '%') OR pt.lastName LIKE CONCAT('%', p_search, '%') OR dr.fullName LIKE CONCAT('%', p_search, '%'))
      AND (p_status IS NULL OR p_status = '' OR a.status = p_status)
      AND (p_doctorID IS NULL OR p_doctorID = 0 OR a.doctorID = p_doctorID)
      AND (p_departmentID IS NULL OR p_departmentID = 0 OR dr.departmentID = p_departmentID)
      AND (p_date_from IS NULL OR a.appointmentDate >= p_date_from)
      AND (p_date_to IS NULL OR a.appointmentDate <= p_date_to)
    ORDER BY a.appointmentDate DESC, a.appointmentTime DESC LIMIT IFNULL(p_limit, 20) OFFSET IFNULL(p_offset, 0);
END$$

DROP PROCEDURE IF EXISTS SP_get_appointment_details$$
CREATE PROCEDURE SP_get_appointment_details(IN p_appointmentID BIGINT)
BEGIN
    SELECT a.appointmentID, a.appointmentDate, a.appointmentTime, a.reason, a.status, a.createdDate,
        pt.patientID, CONCAT(pt.firstName, ' ', pt.lastName) AS patientName, pt.phone AS patientPhone, pt.email AS patientEmail,
        dr.doctorID, dr.fullName AS doctorName, dr.specialization, d.departmentID, d.departmentName
    FROM appointments a JOIN patients pt ON pt.patientID = a.patientID JOIN doctors dr ON dr.doctorID = a.doctorID LEFT JOIN departments d ON d.departmentID = dr.departmentID
    WHERE a.appointmentID = p_appointmentID;
END$$

DROP PROCEDURE IF EXISTS SP_delete_appointment$$
CREATE PROCEDURE SP_delete_appointment(IN p_appointmentID BIGINT, OUT p_affected INT)
BEGIN
    DELETE FROM appointments WHERE appointmentID = p_appointmentID;
    SET p_affected = ROW_COUNT();
END$$

DROP PROCEDURE IF EXISTS sp_get_user_activity_report$$
CREATE PROCEDURE sp_get_user_activity_report()
BEGIN
    SELECT u.id AS user_id, u.name, u.email, u.role, u.is_active AS account_status, u.created_at FROM users u ORDER BY u.created_at DESC;
END$$

DELIMITER ;
SET FOREIGN_KEY_CHECKS=1;
