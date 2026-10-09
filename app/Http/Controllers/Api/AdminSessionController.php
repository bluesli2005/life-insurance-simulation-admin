<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminSessionController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json(['data' => [
            'user_name' => $user->name,
            'role_name' => $user->role->name,
            'can_write_applications' => $user->can('applications.write'),
            'can_delete_applications' => $user->can('applications.delete'),
            'can_manage_users' => $user->can('users.manage'),
        ]]);
    }
}
