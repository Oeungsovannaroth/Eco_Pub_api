<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffShiftRequest;
use App\Http\Requests\UpdateStaffShiftRequest;
use App\Models\StaffShift;

class StaffShiftController extends Controller
{
    public function index()
    {
        return response()->json(StaffShift::with('user')->get());
    }

    public function store(StoreStaffShiftRequest $request)
    {
        $shift = StaffShift::create($request->validated());

      
        $shift->load('user');

        return response()->json($shift, 201);
    }

    public function show($id)
    {
        return response()->json(StaffShift::with('user')->findOrFail($id));
    }

    public function update(UpdateStaffShiftRequest $request, $id)
    {
        $shift = StaffShift::findOrFail($id);
        $shift->update($request->validated());


        $shift->load('user');

        return response()->json($shift);
    }

    public function destroy($id)
    {
        $shift = StaffShift::findOrFail($id);
        $shift->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
