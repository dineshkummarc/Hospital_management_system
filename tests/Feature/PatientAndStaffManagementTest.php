<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PatientAndStaffManagementTest
 *
 * Feature tests for hospital user management (doctors, staff, patients).
 */
class PatientAndStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user to execute CRUD commands
        $this->adminUser = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_a_doctor_user()
    {
        Storage::fake('public');

        $avatarFile = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.store'), [
                'username'              => 'drsmith',
                'name'                  => 'Dr. John Smith',
                'email'                 => 'drsmith@hospital.com',
                'phone'                 => '0711222333',
                'role'                  => 'doctor',
                'password'              => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
                'avatar'                => $avatarFile,
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'drsmith',
            'role'     => 'doctor',
        ]);

        $user = User::where('username', 'drsmith')->first();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    /** @test */
    public function admin_can_create_a_staff_user()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.store'), [
                'username'              => 'staffjane',
                'name'                  => 'Jane Nurse',
                'email'                 => 'jane@hospital.com',
                'phone'                 => '0722333444',
                'role'                  => 'staff',
                'password'              => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'staffjane',
            'role'     => 'staff',
        ]);
    }

    /** @test */
    public function admin_can_create_a_receptionist_user()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.store'), [
                'username'              => 'frontdesk',
                'name'                  => 'Alice Reception',
                'email'                 => 'reception@hospital.com',
                'phone'                 => '0733444555',
                'role'                  => 'receptionist',
                'password'              => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'frontdesk',
            'role'     => 'receptionist',
        ]);
    }

    /** @test */
    public function cannot_create_user_with_duplicate_username()
    {
        // First user
        User::factory()->create([
            'username' => 'drexisting',
            'phone'    => '0700111222',
            'role'     => 'doctor',
        ]);

        // Duplicate username
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.store'), [
                'username'              => 'drexisting',
                'name'                  => 'Another Doctor',
                'email'                 => 'other@hospital.com',
                'phone'                 => '0700333444',
                'role'                  => 'doctor',
                'password'              => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
            ]);

        $response->assertSessionHasErrors('username');
    }

    /** @test */
    public function cannot_create_user_with_duplicate_phone()
    {
        // First user
        User::factory()->create([
            'username' => 'staffone',
            'phone'    => '0711555666',
            'role'     => 'staff',
        ]);

        // Duplicate phone
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.store'), [
                'username'              => 'stafftwo',
                'name'                  => 'Staff Two',
                'email'                 => 'staff2@hospital.com',
                'phone'                 => '0711555666', // Duplicate
                'role'                  => 'staff',
                'password'              => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
            ]);

        $response->assertSessionHasErrors('phone');
    }

    /** @test */
    public function admin_can_toggle_user_status()
    {
        $user = User::factory()->create([
            'role'      => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.toggle', $user));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'        => $user->id,
            'is_active' => false,
        ]);
    }
}
