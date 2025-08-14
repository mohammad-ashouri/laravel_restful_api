<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;

class UserService
{
    public function registerUser(array $inputs)
    {
        try {
            $inputs['password'] = bcrypt($inputs['password']);
            $user = User::create($inputs);
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'data' => $exception->getMessage(),
            ];
            app()[ExceptionHandler::class]->report($exception);
        }

        return [
            'ok' => true,
            'data' => $user
        ];
    }
}
