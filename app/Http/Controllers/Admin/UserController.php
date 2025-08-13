<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\UserDetailsApiResource;
use App\Http\Resources\Admin\User\UsersListApiResource;
use App\Models\User;
use App\RestfulApi\ApiResponse;
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
        return UsersListApiResource::collection($usersQuery->paginate());
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
            return $this->apiResponse(message: 'Something went wrong', status: 500);
        }
//        return $this->apiResponse(message: 'User created successfully',data:$user);

        $response=new ApiResponse();
        $response->setMessage('User created successfully');
        $response->setData($user);
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserDetailsApiResource($user);
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
        try {
            $user->delete();
        } catch (\Throwable $exception) {
            app()[ExceptionHandler::class]->report($exception);
            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    private function apiResponse($message = null, $data = null, $status = 200)
    {
        $body = [];
        !is_null($message) && $body['message'] = $message;
        !is_null($data) && $body['data'] = $data;

        return response()->json($body, $status);
    }
}
