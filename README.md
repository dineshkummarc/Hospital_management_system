# Hospital Management System (HMS)

Full-stack Hospital Management System built using PHP 8 (Laravel 11), MySQL Stored Procedures, REST APIs, Bootstrap styling, jQuery AJAX, and Session/Sanctum Authentication.

## Core Modules

1. **Patients** (`patients`, `SP_*_patient`)
2. **Doctors** (`doctors`, `SP_*_doctor`)
3. **Departments** (`departments`, `SP_*_department`)
4. **Appointments** (`appointments`, `SP_*_appointment`)
5. **Users** (`users`, role-based access)
6. **User Privileges** (RBAC)
7. **Objects** (Permission Objects & Audit Logs)

## Tech Stack & Architecture

- **PHP 8 / Laravel 11** (MVC Architecture)
- **MySQL / MySQL Stored Procedures** (20 procedures across all 4 modules)
- **REST APIs** (JSON Responses: `status`, `message`, `data`)
- **Bootstrap 4** Admin Dashboard Layout
- **jQuery AJAX** (`$.getJSON()`, `$.post()`, `$.ajax()`)
- **Session & Sanctum Authentication**
- **Password Salting & Hashing** (bcrypt / SHA-256)

## Stored Procedures

### Patients
- `SP_check_patient`
- `SP_save_patient`
- `SP_filter_patients`
- `SP_get_patient_details`
- `SP_delete_patient`

### Doctors
- `SP_check_doctor`
- `SP_save_doctor`
- `SP_filter_doctors`
- `SP_get_doctor_details`
- `SP_delete_doctor`

### Departments
- `SP_check_department`
- `SP_save_department`
- `SP_filter_departments`
- `SP_get_department_details`
- `SP_delete_department`

### Appointments
- `SP_check_appointment`
- `SP_save_appointment`
- `SP_filter_appointments`
- `SP_get_appointment_details`
- `SP_delete_appointment`

## Setup Instructions

1. Configure Database in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hospital_management_system
   DB_USERNAME=root
   DB_PASSWORD=
   ```
2. Run Migrations:
   ```bash
   php artisan migrate:fresh --seed
   ```
3. Start local development server:
   ```bash
   php artisan serve
   ```
