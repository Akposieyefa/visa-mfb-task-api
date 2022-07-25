<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @var User
     */
    private User $model;

    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

   //login user
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first(),
                'success' => false
            ], 422);
        }else {
            $cred = request(['email', 'password']);
            if (!auth()->attempt($cred)) {
                return response()->json([
                    'message' => 'Invalid login details',
                    'success' => false
                ], 422);
            }else {
                $token =  auth()->user()->createToken('apiToken')->plainTextToken;
                if ($token) {
                    return response()->json([
                        'token' => $token,
                        'user' => new UserResource(auth()->user()),
                        'token_type' => 'bearer',
                        'success' => true
                    ], 200);
                }
            }
        }
    }

    //loggout user
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out successfully',
            'success' => true
        ], 200);
    }

   //get user profile
    public function userProfile()
    {
        return new UserResource(auth()->user());
    }

}
