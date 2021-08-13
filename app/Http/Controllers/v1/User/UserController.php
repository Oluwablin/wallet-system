<?php

namespace App\Http\Controllers\v1\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = Auth::id();
        if ($id) {
            $customer = User::where('id', '=', $id)
                ->with(['wallet', 'paystack_transaction' => function ($q) {
                    $q->orderBy('paystack_transactions.id', 'desc');
                }])
                ->first();

                return response()->json([
                    'error' => false,
                    'message' => null,
                    'data' => $customer
                ], 200);

        }  else{
            return response()->json([
                'error' => true,
                'message' => 'No User found',
                'data' => null
            ], 400);
        }
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
