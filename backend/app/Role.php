<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** 権限ロールと操作許可フラグを定義する。 */
class Role extends Model
{
    public $timestamps = false;

    protected $casts = [
        'can_write_applications' => 'boolean',
        'can_delete_applications' => 'boolean',
        'can_manage_users' => 'boolean',
    ];
}
