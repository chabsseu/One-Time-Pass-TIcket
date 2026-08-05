<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PriceHistory;

class PriceController extends Controller
{
    public function index(){
        return PriceHistory::orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get();
    }
    public function currentPrice()
    {
        $price = PriceHistory::where('effective_date', '<=', today())
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        return response()->json($price);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        $price = PriceHistory::create($validated);

        return response()->json($price, 201);
    }
}
