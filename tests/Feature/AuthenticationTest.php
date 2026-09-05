<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function validRegisterData(array $overrides = []): array
    {
        return array_merge([
            'name' => '管理 太郎',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_register_creates_user_and_logs_in(): void
    {
        $data = $this->validRegisterData();

        $response = $this->post(route('register'), $data);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_register_fails_validation_when_name_is_missing(): void
    {
        $data = $this->validRegisterData(['name' => '']);

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_register_fails_validation_when_email_is_missing(): void
    {
        $response = $this->post(
            route('register'),
            $this->validRegisterData(['email' => '']),
        );

        $response->assertSessionHasErrors(['email']);
    }

    public function test_register_fails_validation_when_email_format_is_invalid(): void
    {
        $response = $this->post(
            route('register'),
            $this->validRegisterData(['email' => 'aosjaosuak@.com']),
        );

        $response->assertSessionHasErrors(['email']);
    }

    public function test_register_fails_validation_when_password_is_missing(): void
    {
        $response = $this->post(route('register'), $this->validRegisterData(['password' => '', 'password_confirmation' => '']), );
        $response->assertSessionHasErrors(['password']);
    }
    public function test_register_fails_validation_when_password_is_too_short(): void
    {
        $data = $this->validRegisterData([
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_register_fails_validation_when_password_confirmation_does_not_match(): void
    {
        $data = $this->validRegisterData([
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_login_authenticates_user_and_redirects(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_login_fails_validation_when_email_is_missing(): void
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_login_fails_validation_when_password_is_missing(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_login_fails_when_credentials_do_not_match(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        $email = 'rate-limit-test@example.com';

        for ($i = 1; $i <= 6; $i++) {
            $response = $this->post(route('login'), [
                'email' => $email,
                'password' => 'wrong-password',
            ]);
            if ($i < 6) {
                $response->assertStatus(302);
            } else {
                $response->assertStatus(429);
            }
        }
    }

    public function test_logout_logs_out_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $this->assertGuest();
    }
}
