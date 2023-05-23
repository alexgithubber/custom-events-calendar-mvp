<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserCreateRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(UserCreateRequest $request): JsonResponse
    {
        try {
            $userDTO = UserDTO::fromArray($request->validated());
            $createdUserDTO = $this->userService->create($userDTO);

            return response()->json([
                'status' => 'success',
                'access_token' => $createdUserDTO->token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => "Could not create user",
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $this->validateLogin($request->all());

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                return response()->json([
                    'status' => 'success',
                    'authorization' => [
                        'token' => $user->createToken('auth_token')->plainTextToken,
                        'type' => 'bearer',
                    ],
                ]);
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        } catch (ValidationException $validationException) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validationException->validator->errors(),
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => "Could not validate user credentials",
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function validateLogin(array $validationFields): void
    {
        $validator = Validator::make($validationFields, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
