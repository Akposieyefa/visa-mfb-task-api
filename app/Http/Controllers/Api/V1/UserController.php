<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
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

    //crete account
    public function createAccount(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number',
            'password' => ['required', 'confirmed ', Password::min(8)->letters()->mixedCase()->numbers()->symbols()]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'success' => false
            ], 422);
        }else {
            try {
                $user = $this->model->create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'password' => bcrypt($request->password),
                    'role' =>  RoleEnum::CUSTOMER
                ]);
                $user->wallet()->create([
                    'account_number'  => $this->generateAccountNumber(10), 
                    'account_balance' => 0
                ]);
                return response()->json([
                    'message' => 'Account created successfully',
                    'data' => new UserResource($user),
                    'success' => true
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'Sorry unable to create account',
                    'error' => $e->getMessage(),
                    'success' => false
                ], 400);
            }
        }
    }

    //get all customers
    public function getAllCustomers()
    {
        $customers = $this->model->latest()->paginate(20);
        if (count($customers) < 1) {
            return response()->json([
                'message' => 'Sorry no customer found',
                'success' => false
            ], 404);
        }else {
            return UserResource::collection($customers)->additional([
                'message' => "Customers fetched successfully",
                'success' => true
            ], 200);
        }
    }

    //get single customer
    public function getSingleCustomer($id)
    {
        $customer = $this->model->findOrFail($id);
        return (new UserResource($customer))->additional( [
            'message' => "Customer details fetched successfully",
            'success' => true
        ], 200);
    }

      /**
     * @param $number
     * @return string
     */
    public function generateAccountNumber($number): string
    {
        $today = date('YmdHis');
        $characters = '0123456789';
        $main = $today."". $characters;
        $randomString = '';
        for ($i = 0; $i < $number; $i++) {
            $index = rand(0, strlen($main) - 1);
            $randomString .= $main[$index];
        }
        return $randomString;
    }

}
