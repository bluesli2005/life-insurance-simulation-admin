<?php

namespace App\Providers;

use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('applications.write', function (User $user) {
            return $user->role && $user->role->can_write_applications;
        });
        Gate::define('applications.delete', function (User $user) {
            return $user->role && $user->role->can_delete_applications;
        });
        Gate::define('users.manage', function (User $user) {
            return $user->role && $user->role->can_manage_users;
        });
    }
}
