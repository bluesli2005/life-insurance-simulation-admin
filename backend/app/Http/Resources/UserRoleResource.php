<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** ユーザー管理 API の利用者情報を同じ形式で返す。 */
class UserRoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->code,
            'status' => $this->status,
        ];
    }
}
