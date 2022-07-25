<?php

namespace App\Helpers;

use App\Http\Resources\UserResource;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use Illuminate\Http\JsonResponse;
use Stripe\StripeClient;
use Yabacon\Paystack;

/**
 * Payment helper
 */
class PaymentHelper
{
    /**
     * @var Transaction
     */
    private Transaction $transaction_model;
    /**
     * @var Paystack
     */
    private Paystack $paystack;
    /**
     * @var User
     */
    private User $user_model;

    /**
     * @param Transaction $transaction_model
     * @param User $user_model
     */
    public function __construct(Transaction $transaction_model, User $user_model)
    {
        $this->transaction_model = $transaction_model;
        $this->paystack = new Paystack(config('visa-mfb-config.paystack.paystack_secret'));
        $this->user_model = $user_model;
    }

    /**
     * initialize stripe payment
     * @param $request
     * @return JsonResponse
     */
    public function initializePaystackPayment($request): JsonResponse
    {
        $tr = $this->createTransaction($request, 'paystack');
        try {
            $trx = $this->paystack->transaction->initialize(
                [
                    'amount' => $tr->amount * 100, /* in kobo */
                    'email' => auth()->user()->email,
                    'reference' => $tr->trans_ref,
                    'callback_url' => "http://127.0.0.1:8000/api/v1/verify-payment/$tr->trans_ref",
                    'metadata' => [
                        'user_id' => $tr->user_id,
                        'reference' => $tr->trans_ref,
                        'transaction_id' => $tr->id,
                        'total' => $tr->amount,
                    ],
                ]
            );
            return response()->json([
                'message' => 'Paystack transaction link generated successfully',
                'url' => $trx->data->authorization_url,
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sorry there was an error trying to generate payment link',
                'error' => $e->getMessage(),
                'success' => false
            ], 400);
        }
    }

    /**
     * verify paystack unit
     * @param $reference
     * @return JsonResponse
     */
    public function verifyPaystackPayment($reference): JsonResponse
    {
        if (!$reference) {
            return response()->json([
                'message' => 'Sorry No reference token supplied',
                'success' => false
            ], 404);
        }
        try {
            $trx = $this->paystack->transaction->verify([
                'reference' => $reference
            ]);
            $trans_ref = $trx->data->metadata->reference;
            $transType = $this->transaction_model->where('trans_ref', '=', $trans_ref)->where('user_id','=',auth()->user()->id)->first();
            $transType->update([
                'status' => true
            ]);
            auth()->user()->wallet()->increment('account_balance',  $transType->amount);
            return response()->json([
                'message' => 'Transaction successful',
                'data' => new UserResource(auth()->user()),
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sorry unable to verify transaction',
                'error' => $e->getMessage(),
                'success' => false
            ], 400);
        }
    }

    /**
     * create transaction record
     * @param $request
     * @param $gateway
     * @return mixed
     */
    public function createTransaction($request, $gateway): mixed
    {
        $paymentReference = "Trx" . sprintf("%0.9s", str_shuffle(rand(12, 30000) * time()));
        return $this->transaction_model->create([
            'type' => $request->type,
            'user_id' => auth()->user()->id,
            'trans_ref' => $paymentReference,
            'amount' => $request->amount,
            'description' => $request->type,
            'payment_gateway' => $gateway,
        ]);
    }

}
