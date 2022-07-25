<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Helpers\PaymentHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    private PaymentHelper $paymentHelper;

    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    //make payment
    public function makePayment(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'type' => 'required',
            'amount' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'success' => false
            ], 422);
        }else {
            return $this->paymentHelper->initializePaystackPayment($request);
        }
    }

    //verify user transaction
    public function verify($reference)
    {
        return $this->paymentHelper->verifyPaystackPayment($reference);
    }
}
