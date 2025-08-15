<?php

namespace App\Http\Controllers\Admin;

use App\Http\ApiRequests\Admin\Users\UserStoreApiRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\UserDetailsApiResource;
use App\Http\Resources\Admin\User\UsersListApiResource;
use App\Models\User;
use App\RestfulApi\Facades\ApiResponse;
use App\Services\UserService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
    }

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
    public function store(UserStoreApiRequest $request)
    {
        $result = $this->userService->registerUser($request->validated());
        if (!$result->ok) {
            return ApiResponse::withMessage('Something went wrong')->withStatus(500)->build()->response();
        }

        // Internal function
//        return $this->apiResponse(message: 'User created successfully',data:$user);

        // External function
//        $response = new ApiResponse();
//        $response->setMessage('User created successfully');
//        $response->setData($user);
//        $response->setAppends([
//            'new' => 'appended'
//        ]);
//        return $response->response();

        // Builder
//        return (new ApiResponseBuilder())->withMessage('User created successfully')->withData($user)->build()->response();

        //Facade
        return ApiResponse::withMessage('User created successfully')->withData($result->data)->build()->response();
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
