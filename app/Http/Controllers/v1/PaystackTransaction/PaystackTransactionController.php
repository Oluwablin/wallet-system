<?php

namespace App\Http\Controllers\v1\PaystackTransaction;

use Paystack;
use App\Models\PaystackTransaction;
use App\Models\Wallet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth, Validator;
use App\Models\User;

class PaystackTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGateway()
    {
        try{
            return Paystack::getAuthorizationUrl()->redirectNow();
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();

        //dd($paymentDetails['status']);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function initialize(Request $request)
    // {
    //     $credentials = $request->only('email', 'amount');

    //     $rules = [
    //         ['email' => 'required'],
    //         ['amount' => 'required'],
    //         ['amount' => 'numeric'],
    //     ];

    //     $validatorEmail = Validator::make($credentials, $rules[0]);
    //     if($validatorEmail->fails()) {
    //         return response()->json([
    //             'error'=> true,
    //             'message'=> 'Email is required.',
    //             'data' => null
    //         ], 422);
    //     }

    //     $validatorAmount = Validator::make($credentials, $rules[1]);
    //     if($validatorAmount->fails()) {
    //         return response()->json([
    //             'error'=> true,
    //             'message'=> 'Amount is required.',
    //             'data' => null
    //         , 422]);
    //     }

    //     $validatorAmountNumeric = Validator::make($credentials, $rules[2]);
    //     if($validatorAmountNumeric->fails()) {
    //         return response()->json([
    //             'error'=> true,
    //             'message'=> 'Amount must be numeric.',
    //             'data' => null
    //         ], 422);
    //     }

    // 	$amount = 0;
    // 	$payment_reference = "WS".sprintf("%0.9s",str_shuffle(rand(12,30000) * time()));

    //     PaystackTransaction::create([
    //          'user_id'=> Auth::id(),
    //          'reference'=> $payment_reference,
    //          'amount'=> $request->amount,
    //          'status' => 'transfer',
    //          'paid_at' => 'pending',
    //     ]);

    // 	$paystack = new Paystack('sk_test_67acc3e54631a36fd1f862618e909caeb3306511');
    // 	$trx = $paystack->transaction->initialize(
    // 		[
    // 			'amount'=> $request->amount,
    // 			'email'=> $request->email,
    // 			'reference' => $payment_reference,
    // 			'callback_url'=>'http://wallet-system.test/api/v1/tansaction/verify',
    // 			'metadata'=> [
    // 				'user_id'=> Auth::id(),
    // 				'reference'=> $payment_reference,
    // 				'amount'=> $request->amount
    // 			],
    // 		]
    // 	);
	// 	if(!$trx){
	// 	  exit($trx->data->message);
	// 	}
	// 	return $trx->data->authorization_url;
    // }

    public function verifyFundWallet()
    {
        $curl = curl_init();
        $payment_reference = "WS".sprintf("%0.9s",str_shuffle(rand(12, 30000) * time()));

        if(!$payment_reference ) {
            die( 'No reference supplied' );
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($payment_reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "authorization: Bearer sk_test_67acc3e54631a36fd1f862618e909caeb3306511",
                "content-type: application/json",
                "cache-control: no-cache",
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ( $err ){
            // there was an error contacting the Paystack API
            die( 'Curl returned error: ' . $err );
        }

        $tranx = json_decode($response->getBody()->getContents());

        if (!$tranx->status ) {
            // there was an error from the API
            die('API returned error: ' . $tranx->message);
        }

        if ('success' == $tranx->data->status ){
            // transaction was successful...

            header("content-type: application/json");

            echo $response;
            die();

            $user = Auth::user();
            /**
             * Here we check if the user email match the email that was used for the payment
             */
            if ( $tranx->data->customer->email == $user->email ) {
                // if the user email match the transaction email
                $id = $user->id;
                // convert the amount to naira
                $amount = $tranx->data->amount  / 100;

                $trans_ref = $trx->data->data->reference;

                $wallet = Wallet::where('user_id', $id)->first();
                if($wallet){
                    $wallet->update([
                    'amount' => $wallet->amount + $amount
                    ]);
                }

                PaystackTransaction::where('reference', $trans_ref)
                ->first()->update([
                    'paid' => true,
                    'paid_at' => Carbon::now(),
                ]);

                return 'successful';
            }
            else {
                die( 'Invalid transaction: We couldn\'t confirm this transaction' );
            }
        }
    }

    public function verifyTransferWallet()
    {
        $curl = curl_init();
        $payment_reference = "WS".sprintf("%0.9s",str_shuffle(rand(12, 30000) * time()));

        if(!$payment_reference ) {
            die( 'No reference supplied' );
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($payment_reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "authorization: Bearer sk_test_67acc3e54631a36fd1f862618e909caeb3306511",
                "content-type: application/json",
                "cache-control: no-cache",
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ( $err ){
            // there was an error contacting the Paystack API
            die( 'Curl returned error: ' . $err );
        }

        $tranx = json_decode($response->getBody()->getContents());

        if (!$tranx->status ) {
            // there was an error from the API
            die('API returned error: ' . $tranx->message);
        }

        if ('success' == $tranx->data->status ){
            // transaction was successful...

            header("content-type: application/json");

            echo $response;
            die();

            $user = Auth::user();
            /**
             * Here we check if the user email match the email that was used for the payment
             */
            if ( $tranx->data->customer->email == $user->email ) {
                // if the user email match the transaction email
                $id = $user->id;
                // convert the amount to naira
                $amount = $tranx->data->amount  / 100;

                $trans_ref = $trx->data->data->reference;

                PaystackTransaction::where('reference', $trans_ref)
                ->first()->update([
                    'paid' => true,
                    'paid_at' => Carbon::now(),
                ]);

                return 'successful';
            }
            else {
                die( 'Invalid transaction: We couldn\'t confirm this transaction' );
            }
        }
    }
}
