<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RbacApiTest
 *
 * Feature tests for Role-Based Access Control (RBAC) on the Hospital REST API.
 */
class RbacApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->doctorUser = User::factory()->create([
            'role'      => 'doctor',
            'is_active' => true,
        ]);

        $this->staffUser = User::factory()->create([
            'role'      => 'staff',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function unauthenticated_api_requests_are_denied()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    /** @test */
    public function staff_role_cannot_create_users_via_api()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/users', [
                'username' => 'newuser',
                'name'     => 'New User',
                'phone'    => '0711999000',
                'role'     => 'doctor',
                'password' => 'SecurePass123',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function doctor_role_cannot_create_users_via_api()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')
            ->postJson('/api/users', [
                'username' => 'anotheruser',
                'name'     => 'Another User',
                'phone'    => '0722888000',
                'role'     => 'staff',
                'password' => 'SecurePass123',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_role_can_access_dashboard_api()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_user_can_view_own_profile_api()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')
            ->getJson('/api/users/' . $this->doctorUser->id);

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $this->doctorUser->id);
    }
}
