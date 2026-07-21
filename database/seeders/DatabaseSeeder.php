<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Users
        $accounts = [
            [
                'username' => 'admin',
                'name'     => 'Hospital Administrator',
                'email'    => 'admin@hospital.com',
                'phone'    => '0700000001',
                'role'     => 'admin',
                'password' => Hash::make('Admin@1234'),
                'is_active'=> true,
            ],
            [
                'username' => 'receptionist',
                'name'     => 'Hospital Receptionist',
                'email'    => 'receptionist@hospital.com',
                'phone'    => '0700000002',
                'role'     => 'receptionist',
                'password' => Hash::make('Receptionist@1234'),
                'is_active'=> true,
            ],
            [
                'username' => 'staff1',
                'name'     => 'Medical Staff',
                'email'    => 'staff@hospital.com',
                'phone'    => '0700000003',
                'role'     => 'staff',
                'password' => Hash::make('Staff@1234'),
                'is_active'=> true,
            ],
        ];

        foreach ($accounts as $data) {
            User::firstOrCreate(
                ['username' => $data['username']],
                $data
            );
        }

        // 2. Departments
        $cardiology = Department::create([
            'departmentName' => 'Cardiology',
            'description'    => 'Heart and cardiovascular care unit.',
            'status'         => 'active',
        ]);

        $pediatrics = Department::create([
            'departmentName' => 'Pediatrics',
            'description'    => 'Comprehensive medical care for infants, children, and adolescents.',
            'status'         => 'active',
        ]);

        $orthopedics = Department::create([
            'departmentName' => 'Orthopedics',
            'description'    => 'Treatment of musculoskeletal system, bones, and joints.',
            'status'         => 'active',
        ]);

        $general = Department::create([
            'departmentName' => 'General Medicine',
            'description'    => 'Primary healthcare and general medical consultations.',
            'status'         => 'active',
        ]);

        // 3. Doctors
        $doc1 = Doctor::create([
            'fullName'       => 'Dr. Sarah Jenkins',
            'email'          => 's.jenkins@hospital.com',
            'phone'          => '0711000001',
            'specialization' => 'Cardiologist',
            'departmentID'   => $cardiology->departmentID,
            'status'         => 'active',
        ]);

        $doc2 = Doctor::create([
            'fullName'       => 'Dr. Michael Chen',
            'email'          => 'm.chen@hospital.com',
            'phone'          => '0711000002',
            'specialization' => 'Pediatrician',
            'departmentID'   => $pediatrics->departmentID,
            'status'         => 'active',
        ]);

        $doc3 = Doctor::create([
            'fullName'       => 'Dr. Emily Adams',
            'email'          => 'e.adams@hospital.com',
            'phone'          => '0711000003',
            'specialization' => 'Orthopedic Surgeon',
            'departmentID'   => $orthopedics->departmentID,
            'status'         => 'active',
        ]);

        // 4. Patients
        $pt1 = Patient::create([
            'firstName'    => 'John',
            'lastName'     => 'Doe',
            'gender'       => 'male',
            'dateOfBirth'  => '1985-06-15',
            'phone'        => '0722000001',
            'email'        => 'johndoe@gmail.com',
            'address'      => '123 Main Street, Suite 4B',
            'departmentID' => $cardiology->departmentID,
            'status'       => 'active',
        ]);

        $pt2 = Patient::create([
            'firstName'    => 'Alice',
            'lastName'     => 'Smith',
            'gender'       => 'female',
            'dateOfBirth'  => '1992-11-20',
            'phone'        => '0722000002',
            'email'        => 'alicesmith@yahoo.com',
            'address'      => '456 Oak Avenue',
            'departmentID' => $pediatrics->departmentID,
            'status'       => 'active',
        ]);

        // 5. Appointments
        Appointment::create([
            'patientID'       => $pt1->patientID,
            'doctorID'        => $doc1->doctorID,
            'appointmentDate' => today()->toDateString(),
            'appointmentTime' => '10:00:00',
            'reason'          => 'Routine ECG and cardiac checkup',
            'status'          => 'confirmed',
        ]);

        Appointment::create([
            'patientID'       => $pt2->patientID,
            'doctorID'        => $doc2->doctorID,
            'appointmentDate' => today()->addDay()->toDateString(),
            'appointmentTime' => '14:30:00',
            'reason'          => 'General pediatrician consultation',
            'status'          => 'pending',
        ]);

        $this->command->info('✅ Hospital Management System Seeded Successfully!');
        $this->command->info('   Admin User: admin / Admin@1234');
    }
}
