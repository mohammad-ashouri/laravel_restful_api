<?php

namespace App\Services;

use App\Base\ServiceResult;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;

class UserService
{
    public function registerUser(array $inputs): ServiceResult
    {
        try {
            $inputs['password'] = bcrypt($inputs['password']);
            $user = User::create($inputs);
        } catch (\Throwable $exception) {
            app()[ExceptionHandler::class]->report($exception);
            return new ServiceResult(false, $exception->getMessage());
        }

        return new ServiceResult(true, $user);
    }
}
