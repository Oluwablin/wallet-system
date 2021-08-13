<?php

namespace App\Http\Controllers\v1\WalletTransaction;

use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transactionHistory()
    {
        $transactionHistory = WalletTransaction::with(['sender', 'receiver'])->paginate(20);

        if(!$transactionHistory){
            return response()->json([
                'error' => true,
                'message' => 'No Transaction History found',
                'data' => null
            ], 400);
        }

        return response()->json([
            'error' => false,
            'message' => null,
            'data' => $transactionHistory
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

}
