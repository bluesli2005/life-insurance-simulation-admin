<?php

namespace Tests\Feature;

use App\Providers\BroadcastServiceProvider;
use Illuminate\Http\Request;
use Tests\TestCase;

class FrameworkScaffoldTest extends TestCase
{
    public function test_retained_broadcast_provider_registers_broadcast_routes()
    {
        $this->app->register(BroadcastServiceProvider::class);

        $route = app('router')->getRoutes()->match(Request::create('/broadcasting/auth', 'POST'));

        $this->assertSame('broadcasting/auth', $route->uri());
    }

    public function test_console_schedule_command_boots_the_application_schedule()
    {
        $this->artisan('schedule:run')->assertExitCode(0);
    }
}
