<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;

/** パスワード再設定の結果を API 用の JSON 応答に変換する。 */
trait FormatsPasswordBrokerResponses
{
    protected function passwordBrokerSuccessResponse($response)
    {
        return response()->json(['message' => trans($response)]);
    }

    protected function passwordBrokerFailedResponse($response)
    {
        return response()->json([
            'errors' => ['email' => [trans($response)]],
        ], $response === PasswordBrokerContract::RESET_THROTTLED ? 429 : 422);
    }
}
