<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserRoleResource;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** ユーザーの権限と利用状態を管理する。 */
class UserRoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => UserRoleResource::collection(User::with('role')->orderBy('id')->get())->resolve(),
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
            // 同時更新でも最高管理者がゼロにならないよう、対象行をロックする。
            $superAdminIds = User::where('role_id', $superRole->id)
                ->where('status', User::STATUS_ACTIVE)->lockForUpdate()->pluck('id');
            // ロック取得後に対象ユーザーを読み直し、古い権限で判定しない。
            $user->refresh()->load('role');

            // 初期管理者と最後の有効な最高管理者の降格を禁止する。
            if ($user->role_id === $superRole->id && $selectedRole->id !== $superRole->id &&
                ($this->isReservedAdmin($user) || $superAdminIds->count() === 1)) {
                return response()->json(['message' => __('errors.api.super_admin_role_required')], 422);
            }

            $user->update(['role_id' => $selectedRole->id]);
            $user->setRelation('role', $selectedRole);

            return response()->json(['data' => (new UserRoleResource($user))->resolve()]);
        });
    }

    public function updateStatus(Request $request, User $user)
    {
        // ユーザー管理権限だけでは状態変更できず、最高管理者に限定する。
        abort_unless($request->user()->role->code === User::ROLE_SUPER_ADMIN, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_DELETED])],
        ]);

        return DB::transaction(function () use ($request, $user, $data) {
            $user->refresh()->load('role');

            // 自分自身と初期管理者の無効化を防ぐ。
            if ($data['status'] === User::STATUS_DELETED &&
                ($user->id === $request->user()->id || $this->isReservedAdmin($user))) {
                return response()->json(['message' => __('errors.api.user_cannot_be_deleted')], 422);
            }

            $user->update(['status' => $data['status']]);

            return response()->json(['data' => (new UserRoleResource($user))->resolve()]);
        });
    }

    private function isReservedAdmin(User $user)
    {
        return $user->email === 'admin@example.com';
    }
}
