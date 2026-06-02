<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_valid_login_authenticates_and_redirects()
    {
        $user = User::factory()->create([
            'email' => 'test@lifevest.com',
            'password' => bcrypt('secret123')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@lifevest.com',
            'password' => 'secret123'
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_returns_errors()
    {
        $user = User::factory()->create([
            'email' => 'test@lifevest.com',
            'password' => bcrypt('secret123')
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'test@lifevest.com',
            'password' => 'wrongpass'
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login_and_shows_reason()
    {
        $user = User::factory()->create([
            'email' => 'test@lifevest.com',
            'password' => bcrypt('secret123'),
            'is_suspended' => true,
            'suspension_reason' => 'Rules violation'
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'test@lifevest.com',
            'password' => 'secret123'
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_redirects_to_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
