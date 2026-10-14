<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PasswordInputTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider invalidNewPasswords */
    public function test_auth_endpoints_reject_invalid_new_passwords($endpoint, $password, $confirmation)
    {
        $user = $this->createUser();
        $payload = ['password' => $password, 'password_confirmation' => $confirmation];

        if ($endpoint === 'register') {
            $payload += ['name' => '新規利用者', 'email' => 'new@example.com'];
        } elseif ($endpoint === 'password/change') {
            $this->actingAs($user);
            $payload['current_password'] = 'old-password-123';
        } else {
            $payload += ['email' => $user->email, 'token' => Password::broker()->createToken($user)];
        }

        $this->postJson('/api/v1/auth/'.$endpoint, $payload)
            ->assertStatus(422)->assertJsonValidationErrors('password');

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
        if ($endpoint === 'password/reset') {
            $this->assertTrue(Password::broker()->tokenExists($user, $payload['token']));
        }
    }

    public function invalidNewPasswords(): array
    {
        $inputs = [
            'short' => ['short', 'short'],
            'unconfirmed' => ['new-password-123', 'different-password'],
            'array' => [array_fill(0, 8, 'x'), array_fill(0, 8, 'x')],
            'integer' => [12345678, 12345678],
        ];
        $cases = [];
        foreach (['register', 'password/change', 'password/reset'] as $endpoint) {
            foreach ($inputs as $name => $input) {
                $cases[$endpoint.' '.$name] = [$endpoint, $input[0], $input[1]];
            }
        }

        return $cases;
    }

    public function test_password_with_surrounding_spaces_can_be_used_to_login_and_change()
    {
        $user = $this->createUser(' old-password-123 ');
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email, 'password' => ' old-password-123 ',
        ])->assertNoContent();

        $this->postJson('/api/v1/auth/password/change', [
            'current_password' => ' old-password-123 ',
            'password' => ' new-password-123 ',
            'password_confirmation' => ' new-password-123 ',
        ])->assertOk();

        $this->assertTrue(Hash::check(' new-password-123 ', $user->fresh()->password));
        $this->assertFalse(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    /** @dataProvider nonStringValues */
    public function test_password_change_rejects_non_string_current_password($value)
    {
        $user = $this->createUser();
        $this->actingAs($user)->postJson('/api/v1/auth/password/change', [
            'current_password' => $value,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(422)->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    /** @dataProvider nonStringValues */
    public function test_password_reset_rejects_non_string_token_without_consuming_valid_token($value)
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);
        $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email, 'token' => $value,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(422)->assertJsonValidationErrors('token');

        $this->assertTrue(Password::broker()->tokenExists($user, $token));
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
        $this->assertGuest();
    }

    public function nonStringValues(): array
    {
        return ['array' => [['invalid']], 'integer' => [12345678]];
    }

    public function test_validation_redirect_does_not_flash_any_password_fields()
    {
        $request = Request::create('/password/change', 'POST', [
            'name' => '利用者', 'current_password' => 'old-secret',
            'password' => 'new-secret', 'password_confirmation' => 'new-secret',
        ]);
        $request->setLaravelSession($this->app['session.store']);
        $exception = ValidationException::withMessages(['password' => ['Invalid password.']]);
        $exception->redirectTo('/password/change');

        $response = $this->app->make(ExceptionHandler::class)->render($request, $exception);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(['name' => '利用者'], session('_old_input'));
    }

    private function createUser($password = 'old-password-123')
    {
        return User::create([
            'name' => '利用者', 'email' => 'existing@example.com',
            'password' => Hash::make($password),
        ])->fresh();
    }
}
