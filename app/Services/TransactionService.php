<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;
use App\Services\EventPublisherService;
// use Illuminate\Http\Client\Response;
// use Illuminate\Http\Response;

class TransactionService
{


    public function deposit(int $userId, float $amount): array
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $userId]);
        $wallet->balance += $amount;
        $wallet->save();

        $publisher = new EventPublisherService();
        $publisher->publish('wallets', [
            'id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'balance' => $wallet->balance
        ]);

        return 
            array(
                "status" => 'success',
                "message" => 'Depósito realizado com sucesso!',
                "balance" => $wallet->balance
            )
        ;
    }

    public function transfer(int $payerId, int $payeeId, float $amount): array
    {
        return DB::transaction(function () use ($payerId, $payeeId, $amount) {
            $payer = User::findOrFail($payerId);
            $payee = User::findOrFail($payeeId);

            // Verifica se o pagador tem saldo suficiente
            $payerWallet = Wallet::where('user_id', $payerId)->firstOrFail();
            if ($payerWallet->balance < $amount) {
                throw new Exception("Saldo insuficiente.");
            }

            // Lojistas não podem transferir dinheiro
            if ($payer->type === 'merchant') {
                throw new Exception("Lojistas não podem realizar transferências.");
            }

            // Verifica autorização externa
            $authorization = Http::get('https://util.devi.tools/api/v2/authorize')->json();
            if ($authorization['message'] !== 'Autorizado') {
                throw new Exception("Transação não autorizada.");
            }
            $publisher = new EventPublisherService();

            // Realiza a transferência
            $payerWallet->balance -= $amount;
            $payerWallet->save();

            $publisher->publish('wallets', [
                'id' => $payerWallet->id,
                'user_id' => $payerId,
                'balance' => $payerWallet->balance
            ]);

            $payeeWallet = Wallet::firstOrCreate(['user_id' => $payeeId]);
            $payeeWallet->balance += $amount;
            $payeeWallet->save();

            $publisher->publish('wallets', [
                'id' => $payeeWallet->id,
                'user_id' => $payeeId,
                'balance' => $payeeWallet->balance
            ]);

            // Registra a transação
            $transaction = Transaction::create([
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'amount' => $amount
            ]);
          
            $publisher->publish('transactions', [
                'id' => $transaction->id,
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'amount' => $amount
            ]);

            // Notifica o recebedor
            Http::post('https://util.devi.tools/api/v1/notify', [
                'message' => "Você recebeu um pagamento de R$ {$amount}."
            ]);

            return [
                'status' => 'success',
                'message' => 'Transferência realizada com sucesso!',
                'balance' => $payerWallet->balance
            ];
        });
    }
}
