<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Wallet, Transaction};
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id()]);
        return response()->json(['balance' => $wallet->balance]);
    }

    public function transactions()
    {
        return response()->json(Transaction::where('user_id', Auth::id())->latest()->get());
    }

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
            return response()->json(['message' => 'Invalid product ID'], 400);
        }

        $coins = $coinMapping[$request->product_id];

        // [Optional: Validate purchase with Google if needed]
        // For now, we'll assume client already verified


        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $wallet->balance += $coins;
        $wallet->save();


        Transaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $coins,
            'description' => 'Coins purchased via Play Store: ' . $coins
        ]);

        return response()->json(['message' => 'Coins added successfully', 'balance' => $wallet->balance]);
    }
}