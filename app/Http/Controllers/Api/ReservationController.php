<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\PubTable;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
class ReservationController extends Controller
{
    // public function index(): JsonResponse
    // {
    //     $reservations = Reservation::with('table')->latest()->get();

    //     return response()->json([
    //         'message' => 'Reservation list',
    //         'data' => $reservations,
    //     ]);
    // }
public function index(): JsonResponse
{
    $user = auth::user(); // get the logged-in user

    if ($user->role === 'customer') {
        // Customers only see their own reservations
        $reservations = Reservation::with('table')
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    } else {
        // Staff and Admin see all reservations
        $reservations = Reservation::with('table')->latest()->get();
    }

    return response()->json([
        'message' => 'Reservation list',
        'data' => $reservations,
    ]);
}

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $table = PubTable::find($data['table_id']);

        if (! $table) {
            return response()->json([
                'message' => 'Table not found',
            ], 404);
        }

        if ($table->status !== 'available') {
            return response()->json([
                'message' => 'This table is not available for reservation.',
            ], 422);
        }

        $reservation = Reservation::create($data);

        if (in_array($reservation->status, ['pending', 'confirmed'])) {
            $table->update([
                'status' => 'reserved',
            ]);
        }

        return response()->json([
            'message' => 'Reservation created successfully',
            'data' => $reservation->load('table'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $reservation = Reservation::with('table')->findOrFail($id);

        return response()->json([
            'message' => 'Reservation detail',
            'data' => $reservation,
        ]);
    }

    public function update(UpdateReservationRequest $request, string $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);
        $oldTableId = $reservation->table_id;
        $oldStatus = $reservation->status;

        $data = $request->validated();

        if (isset($data['table_id'])) {
            $newTable = PubTable::find($data['table_id']);

            if (! $newTable) {
                return response()->json([
                    'message' => 'Table not found',
                ], 404);
            }

            if (
                $data['table_id'] !== $oldTableId &&
                $newTable->status !== 'available'
            ) {
                return response()->json([
                    'message' => 'Selected table is not available.',
                ], 422);
            }
        }

        $reservation->update($data);

        $newTableId = $reservation->table_id;
        $newStatus = $reservation->status;

        if ($oldTableId !== $newTableId) {
            $oldTable = PubTable::find($oldTableId);
            $newTable = PubTable::find($newTableId);

            if ($oldTable) {
                $oldTable->update(['status' => 'available']);
            }

            if ($newTable && in_array($newStatus, ['pending', 'confirmed'])) {
                $newTable->update(['status' => 'reserved']);
            }
        } else {
            $table = PubTable::find($newTableId);

            if ($table) {
                if (in_array($newStatus, ['pending', 'confirmed'])) {
                    $table->update(['status' => 'reserved']);
                }

                if (in_array($newStatus, ['cancelled', 'completed'])) {
                    $table->update(['status' => 'available']);
                }
            }
        }

        return response()->json([
            'message' => 'Reservation updated successfully',
            'data' => $reservation->load('table'),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);

        $table = PubTable::find($reservation->table_id);
        if ($table) {
            $table->update([
                'status' => 'available',
            ]);
        }

        $reservation->delete();

        return response()->json([
            'message' => 'Reservation deleted successfully',
        ]);
    }

    public function updateStatus(string $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);
        $table = PubTable::find($reservation->table_id);
        $status = request('status');

        if (! in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
            return response()->json([
                'message' => 'Invalid reservation status',
            ], 422);
        }

        $reservation->update([
            'status' => $status,
        ]);

        if ($table) {
            if (in_array($status, ['pending', 'confirmed'])) {
                $table->update(['status' => 'reserved']);
            }

            if (in_array($status, ['cancelled', 'completed'])) {
                $table->update(['status' => 'available']);
            }
        }

        return response()->json([
            'message' => 'Reservation status updated successfully',
            'data' => $reservation,
        ]);
    }
}