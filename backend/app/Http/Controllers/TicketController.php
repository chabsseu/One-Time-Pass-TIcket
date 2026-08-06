<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index()
    {
        return Ticket::latest()->get();
    }

    public function nextControlNumber()
    {
        $last = Ticket::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) $last->control_no) + 1 : 1;

        return response()->json([
            'control_no' => str_pad($nextNumber, 7, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'plate_number' => 'required|string|max:20',
            'driver' => 'required|string|max:255',
            'company' => 'required|string|max:255',
        ]);

        $ticket = DB::transaction(function () use ($validated) {
            $last = Ticket::lockForUpdate()->orderByDesc('id')->first();
            $nextNumber = $last ? ((int) $last->control_no) + 1 : 1;
            $validated['control_no'] = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

            $latestPrice = PriceHistory::where('effective_date', '<=', now())
                ->orderByDesc('effective_date')
                ->orderByDesc('id')  // ← added tie-breaker
                ->first();
            $validated['amount'] = $latestPrice->amount ?? 30.00;

            return Ticket::create($validated);
        });

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        return $ticket;
    }
}