<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Registers a new user
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try { // request validation
            $validator = Validator::make($request->all(), [
                "name" => "required|string",
                "email" => "required|email|string|unique:users,email",
                "password" => [
                    "required", "string", "confirmed", "min:8", "max:64"
                ]
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'msg' => 'malformed request',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            // register and create new user
            $user = DB::transaction(function () use ($request) {
                /** @var \App\Models\User $user */
                $user = new User([
                    "name" => $request->input('name'),
                    "email" => $request->input('email'),
                    "password" => Hash::make($request->input("password"))
                ]);
                $user->save();
                return $user;
            }, 10);

            return response()->json([
                'msg' => 'user created',
                'token' => $user->createToken('api_token')->plainTextToken
            ], Response::HTTP_CREATED);
        }
        // handle internal server error
        catch (\Throwable $th) {
            return response()->json([
                'msg' => 'something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request)
    {
        try { // request validation
            $validator = Validator::make($request->all(), [
                "email" => "required|email|string|exists:users,email",
                "password" => [
                    "required", "string", "min:8", "max:64"
                ]
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'msg' => 'malformed request',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("api_token")->plainTextToken
            ], 200);
        }
        // handle internal server error
        catch (\Throwable $th) {
            return response()->json([
                'msg' => 'something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * logs out the current user
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            /** @var \App\Models\User $user */
            $user = request()->user();
            // delete current accesstoken
            $user->tokens()
                ->where('id', $user->currentAccessToken()->id)
                ->delete();
            return response()->json([
                'msg' => 'successfully logged out'
            ], Response::HTTP_OK);
        }
        // handle internal server error
        catch (\Throwable $th) {
            return response()->json([
                'msg' => 'something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
