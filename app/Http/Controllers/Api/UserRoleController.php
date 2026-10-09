<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => User::with('role')->orderBy('id')->get()->map(function (User $user) {
                return $this->userData($user);
            }),
            'roles' => Role::orderBy('id')->get([
                'code', 'name', 'can_write_applications', 'can_delete_applications', 'can_manage_users',
            ]),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::exists('roles', 'code')],
        ]);
        $selectedRole = Role::where('code', $data['role'])->firstOrFail();
        $superRole = Role::where('code', User::ROLE_SUPER_ADMIN)->firstOrFail();

        return DB::transaction(function () use ($user, $selectedRole, $superRole) {
            $superAdminIds = User::where('role_id', $superRole->id)
                ->where('status', User::STATUS_ACTIVE)->lockForUpdate()->pluck('id');
            $user->refresh()->load('role');

            if ($user->role_id === $superRole->id && $selectedRole->id !== $superRole->id &&
                ($user->email === 'admin@example.com' || $superAdminIds->count() === 1)) {
                return response()->json(['message' => 'この管理者の最高権限は解除できません。'], 422);
            }

            $user->update(['role_id' => $selectedRole->id]);
            $user->setRelation('role', $selectedRole);

            return response()->json(['data' => $this->userData($user)]);
        });
    }

    public function updateStatus(Request $request, User $user)
    {
        abort_unless($request->user()->role->code === User::ROLE_SUPER_ADMIN, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_DELETED])],
        ]);

        return DB::transaction(function () use ($request, $user, $data) {
            $user->refresh()->load('role');

            if ($data['status'] === User::STATUS_DELETED &&
                ($user->id === $request->user()->id || $user->email === 'admin@example.com')) {
                return response()->json(['message' => 'このユーザーは削除できません。'], 422);
            }

            $user->update(['status' => $data['status']]);

            return response()->json(['data' => $this->userData($user)]);
        });
    }

    private function userData(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->code,
            'status' => $user->status,
        ];
    }
}
