<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Wallet, Transaction};
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;

class WalletController extends Controller
{
    use ApiResponseTrait;

    /**
     * Fetch user wallet balance
     */
    public function index()
    {
        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id()]);

        return $this->successResponse('WALLET_FETCHED', [
            'balance' => $wallet->balance
        ]);
    }

    /**
     * Fetch user's transaction history
     */
    public function transactions()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->get();

        return $this->successResponse('TRANSACTION_LIST', $transactions);
    }

    /**
     * Purchase coins and update wallet
     */
    public function purchaseCoins(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'purchase_token' => 'required|string',
        ]);

        $user = Auth::user();

        $coinMapping = [
            'coins_10' => 10,
            'coins_50' => 50,
            'coins_100' => 100,
        ];

        if (!isset($coinMapping[$request->product_id])) {
            return $this->errorResponse(ResponseCode::BAD_REQUEST, 'INVALID_PRODUCT');
        }

        $coins = $coinMapping[$request->product_id];

        // (Optional) Validate with Google Play API if needed

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $wallet->balance += $coins;
        $wallet->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $coins,
            'description' => 'Coins purchased via Play Store: ' . $coins
        ]);

        return $this->successResponse('COINS_ADDED', [
            'balance' => $wallet->balance
        ]);
    }
}
