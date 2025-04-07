<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;


class TransactionController extends Controller
{

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function deposit(Request $request): JsonResponse
    {
        // dd($request->user_id);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);
    

        return response()->json($this->transactionService->deposit($data['user_id'], $request['amount']));
    }

    public function transfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payer_id' => 'required|exists:users,id',
            'payee_id' => 'required|exists:users,id|different:payer_id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        return response()->json($this->transactionService->transfer($data['payer_id'], $data['payee_id'], $data['amount']));
    }

}
