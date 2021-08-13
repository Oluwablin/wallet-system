<?php

namespace App\Http\Controllers\v1\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Auth, Validator;
use Paystack;
use App\Models\PaystackTransaction;
use Carbon\Carbon;

class WalletController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fundWallet(Request $request)
    {
        // Get the currently authenticated user's ID...
        $id = Auth::id();

        $customer = $this->showUserWallet($id);

        $credentials = $request->only('email', 'amount');

        $rules = [
            ['email' => 'required'],
            ['amount' => 'required'],
            ['amount' => 'numeric'],
        ];

        $validatorEmail = Validator::make($credentials, $rules[0]);
        if($validatorEmail->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Email is required.',
                'data' => null
            ], 422);
        }

        $validatorAmount = Validator::make($credentials, $rules[1]);
        if($validatorAmount->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Amount is required.',
                'data' => null
            , 422]);
        }

        $validatorAmountNumeric = Validator::make($credentials, $rules[2]);
        if($validatorAmountNumeric->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Amount must be numeric.',
                'data' => null
            ], 422);
        }

    	$amount = 0;
    	$payment_reference = "WS".sprintf("%0.9s",str_shuffle(rand(12,30000) * time()));

        PaystackTransaction::create([
             'user_id'=> Auth::id(),
             'reference'=> $payment_reference,
             'amount'=> $request->amount,
             'status' => 'fund wallet',
             'paid_at' => 'pending',
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'amount'=> 100 * $request->amount, //because amount should be in kobo
    		'email'=> $request->email,
            'reference'=> $payment_reference,
            'callback_url'=>'http://127.0.0.1:9000/api/v1/tansaction/verify/fund',
            'metadata'=> [
                'user_id'=> Auth::id(),
                'reference'=> $payment_reference,
                'amount'=> $request->amount
            ],
        ]),
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer sk_test_67acc3e54631a36fd1f862618e909caeb3306511",
            "content-type: application/json",
            "cache-control: no-cache",
        ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if( $err ){
        // there was an error contacting the Paystack API
        die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);
        //return $tranx;

        if (!$tranx['status'] ) {
        // there was an error from the API
        print_r('API returned error: ' . $tranx['message']);
        }
		return $tranx['data']['authorization_url'];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transferFunds(Request $request)
    {
        $credentials = $request->only('email', 'amount');

        $rules = [
            ['email' => 'required'],
            ['amount' => 'required'],
            ['amount' => 'numeric'],
        ];

        $validatorEmail = Validator::make($credentials, $rules[0]);
        if($validatorEmail->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Email is required.',
                'data' => null
            ], 422);
        }

        $validatorAmount = Validator::make($credentials, $rules[1]);
        if($validatorAmount->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Amount is required.',
                'data' => null
            , 422]);
        }

        $validatorAmountNumeric = Validator::make($credentials, $rules[2]);
        if($validatorAmountNumeric->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Amount must be numeric.',
                'data' => null
            ], 422);
        }
        if ($request->amount <= 0) {
            return response()->json([
                'error' => true,
                'message' => 'Amount to transfer CANNOT be less or equals 0.',
                'data' => null
            ], 400);
        }

        $id = Auth::id();
        $sender = $this->showUserWallet($id);
        //return $sender;

        $receiver = User::where('email', $request->email)->with(['wallet'])->first();



        $amount = 0;
    	$payment_reference = "WS".sprintf("%0.9s",str_shuffle(rand(12, 30000) * time()));

        PaystackTransaction::create([
             'user_id'=> $receiver->id,
             'reference'=> $payment_reference,
             'amount'=> $request->amount,
             'status' => 'transfer wallet',
             'paid_at' => 'pending',
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'amount'=> 100 * $request->amount, //because amount should be in kobo
    		'email'=> $request->email,
            'reference'=> $payment_reference,
            'callback_url'=>'http://127.0.0.1:9000/api/v1/tansaction/verify/transfer',
            'metadata'=> [
                'user_id'=> Auth::id(),
                'reference'=> $payment_reference,
                'amount'=> $request->amount
            ],
        ]),
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer sk_test_67acc3e54631a36fd1f862618e909caeb3306511",
            "content-type: application/json",
            "cache-control: no-cache",
        ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if( $err ){
        // there was an error contacting the Paystack API
        die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);
        //return $tranx;

        if (!$tranx['status'] ) {
        // there was an error from the API
        print_r('API returned error: ' . $tranx['message']);
        }
		return $tranx['data']['authorization_url'];

        if ($receiver) {
            if ($this->updateWallet($sender->wallet->id, $receiver->wallet->id, $request->amount)) {
                return response()->json([
                    'error' => false,
                    'message' => 'Transfer Successful!',
                    'data' => null
                ], 201);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Insufficient funds',
                    'data' => null
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Customer not found on this platform',
                'data' => null
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    protected function updateWallet($sender_wallet_id, $receiver_wallet_id, $amount)
    {
        try {
            $sender_wallet = Wallet::where('id', $sender_wallet_id)->first();
            if ($sender_wallet->balance >= $amount) {
                DB::beginTransaction();

                $sender_balance = $sender_wallet->balance - $amount;
                $sender_wallet->update(['balance' => $sender_balance]);

                $receiver_wallet = Wallet::where('id', $receiver_wallet_id)->first();
                $receiver_balance = $receiver_wallet->balance + $amount;
                $receiver_wallet->update(['balance' => $receiver_balance]);

                WalletTransaction::create([
                    'sender_id' => $sender_wallet_id,
                    'receiver_id' => $receiver_wallet_id,
                    'amount' => $amount
                ]);

                DB::commit();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            DB::rollback();
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function showUserWallet($id)
    {
        return User::where('id', $id)->with(['wallet'])->first();
    }
}
