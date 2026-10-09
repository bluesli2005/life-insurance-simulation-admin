<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetainedAuthScaffoldTest extends TestCase
{
    use RefreshDatabase;

    public function test_retained_auth_controllers_register_auth_middleware()
    {
        $confirm = new ConfirmPasswordController();
        $verification = new VerificationController();
        $registration = new RegisterController();

        $this->assertSame('auth', $confirm->getMiddleware()[0]['middleware']);
        $this->assertSame('auth', $verification->getMiddleware()[0]['middleware']);
        $this->assertSame('guest', $registration->getMiddleware()[0]['middleware']);
    }

    public function test_retained_registration_controller_validates_and_creates_a_user()
    {
        $controller = new RegisterController();
        $data = [
            'name' => '登録 太郎',
            'email' => 'register@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = $this->invokeProtected($controller, 'validator', [$data]);
        $this->assertTrue($validator->passes());

        $user = $this->invokeProtected($controller, 'create', [$data]);
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotSame($data['password'], $user->password);
        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    private function invokeProtected($object, $method, array $arguments)
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
