<?php

namespace App\Http\Controllers\API\Agent;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Find the root owner ID (Support Agent -> Manager -> Owner)
            $ownerId = $user->id;
            $currentUser = $user;
            
            while ($currentUser->parent_id) {
                $ownerId = $currentUser->parent_id;
                $currentUser = \App\Models\User::find($currentUser->parent_id);
            }

            $query = \App\Models\Ticket::where('owner_id', $ownerId);

            // Filtering
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('source')) {
                $query->where('source', $request->source);
            }
            if ($request->has('search')) {
                $query->where('ticket_number', 'like', '%' . $request->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                    ->orWhere('subject', 'like', '%' . $request->search . '%');
            }

            $tickets = $query->latest()->get();

            return $this->sendResponse(
                \App\Http\Resources\API\Agent\TicketResource::collection($tickets), 
                'Tickets fetched successfully.'
            );
        } catch (\Exception $e) {
            return $this->sendError('Failed to fetch tickets.', [$e->getMessage()]);
        }
    }
}
