<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt('oldpassword'),
        ]);
    }

    public function test_profile_page_loads()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.settings'));
        $response->assertStatus(200);
        $response->assertViewHas('user');
    }

    public function test_guest_cannot_access_profile()
    {
        $this->get(route('profile.settings'))->assertRedirect(route('login'));
    }

    public function test_update_password_success()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(
            password_verify('newpassword123', $this->user->fresh()->password)
        );
    }

    public function test_update_password_fails_with_wrong_current_password()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_update_password_fails_with_mismatched_confirmation()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
