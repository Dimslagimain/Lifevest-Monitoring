<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
    }

    public function test_guest_cannot_access_user_management()
    {
        $this->get(route('superadmin.users'))->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_user_management()
    {
        $this->actingAs($this->regularUser)
            ->get(route('superadmin.users'))
            ->assertStatus(403);
    }

    public function test_superadmin_can_view_users()
    {
        $this->actingAs($this->superadmin)
            ->get(route('superadmin.users'))
            ->assertStatus(200);
    }

    public function test_superadmin_can_create_user()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'role' => 'user',
        ]);
    }

    public function test_superadmin_cannot_create_user_with_duplicate_email()
    {
        $this->actingAs($this->superadmin);

        User::factory()->create(['email' => 'exists@test.com']);

        $response = $this->post(route('superadmin.users.store'), [
            'name' => 'Duplicate',
            'email' => 'exists@test.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_superadmin_can_update_user()
    {
        $this->actingAs($this->superadmin);

        $user = User::factory()->create([
            'name' => 'Old Name',
            'role' => 'user',
        ]);

        $response = $this->put(route('superadmin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'admin',
        ]);
    }

    public function test_superadmin_can_suspend_user()
    {
        $this->actingAs($this->superadmin);

        $user = User::factory()->create(['role' => 'user']);

        $response = $this->post(route('superadmin.users.suspend', $user), [
            'reason' => 'Violation of policy',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_suspended' => true,
            'suspension_reason' => 'Violation of policy',
        ]);
    }

    public function test_superadmin_cannot_suspend_self()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.users.suspend', $this->superadmin), [
            'reason' => 'Test reason',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->superadmin->id,
            'is_suspended' => false,
        ]);
    }

    public function test_superadmin_can_unsuspend_user()
    {
        $this->actingAs($this->superadmin);

        $user = User::factory()->create([
            'role' => 'user',
            'is_suspended' => true,
            'suspension_reason' => 'Some reason',
        ]);

        $response = $this->post(route('superadmin.users.unsuspend', $user));

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_suspended' => false,
            'suspension_reason' => null,
        ]);
    }

    public function test_superadmin_can_delete_user()
    {
        $this->actingAs($this->superadmin);

        $user = User::factory()->create(['role' => 'user']);

        $response = $this->delete(route('superadmin.users.destroy', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_superadmin_cannot_delete_self()
    {
        $this->actingAs($this->superadmin);

        $response = $this->delete(route('superadmin.users.destroy', $this->superadmin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);
    }
}
