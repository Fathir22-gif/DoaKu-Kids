<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // REGISTER
    // =========================================================

    /** @test */
    public function register_page_loads_successfully(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => 'Anak Soleh',
            'email'                 => 'anak@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'email' => 'anak@example.com',
            'name'  => 'Anak Soleh',
        ]);
        $this->assertAuthenticated();
    }

    /** @test */
    public function register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'anak@example.com']);

        $response = $this->post(route('register'), [
            'name'                  => 'Anak Lain',
            'email'                 => 'anak@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function register_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => 'Anak Soleh',
            'email'                 => 'anak@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'berbeda123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function register_fails_with_missing_required_fields(): void
    {
        $response = $this->post(route('register'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    // =========================================================
    // LOGIN
    // =========================================================

    /** @test */
    public function login_page_loads_successfully(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'anak@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'anak@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'anak@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'anak@example.com',
            'password' => 'salah_password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post(route('login'), [
            'email'    => 'tidakada@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function login_fails_with_missing_fields(): void
    {
        $response = $this->post(route('login'), []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    /** @test */
    public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /** @test */
    public function guest_cannot_access_protected_routes(): void
    {
        $this->get(route('favorites.index'))->assertRedirect(route('login'));
        $this->get(route('memorization.index'))->assertRedirect(route('login'));
    }
}
