<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required',
            'plate_number' => 'required',
            'driver' => 'required',
            'company' => 'required',
            'amount' => 'required|numeric',
        ]);

        // Get the latest ticket
        $lastTicket = Ticket::latest('id')->first();

        // First ticket starts at 1
        $nextNumber = $lastTicket ? $lastTicket->id + 1 : 1;

        // Format as 0000001
        $validated['control_no'] = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        $ticket = Ticket::create($validated);

        return response()->json($ticket, 201);
    }

    public function nextControlNumber()
    {
        $lastTicket = Ticket::latest('id')->first();

        $nextNumber = $lastTicket ? $lastTicket->id + 1 : 1;

        return response()->json([
            'control_no' => str_pad($nextNumber, 7, '0', STR_PAD_LEFT)
        ]);
    }

}