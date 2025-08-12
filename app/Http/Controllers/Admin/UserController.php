<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usersQuery = User::query();
        if (\request()->has('email')) {
            $usersQuery->whereEmail(\request()->get('email'));
        }
        return response()->json([
            'data' => $usersQuery->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'first_name' => ['required', 'string', 'min:1', 'max:255'],
                'last_name' => ['required', 'string', 'min:1', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $inputs = $validator->validated();
            $inputs['password'] = bcrypt($inputs['password']);
            $user = User::create($inputs);
        } catch (\Throwable $exception) {
            app()[ExceptionHandler::class]->report($exception);
            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'first_name' => ['required', 'string', 'min:1', 'max:255'],
                'last_name' => ['required', 'string', 'min:1', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $inputs = $validator->validated();

            if (isset($inputs['password'])) {
                $inputs['password'] = bcrypt($inputs['password']);
            }
            $user = $user->update($inputs);
        } catch (\Throwable $exception) {
            app()[ExceptionHandler::class]->report($exception);
            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
