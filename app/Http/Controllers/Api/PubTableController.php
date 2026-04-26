<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePubTableRequest;
use App\Http\Requests\UpdatePubTableRequest;
use App\Models\PubTable;
use Illuminate\Http\JsonResponse;

class PubTableController extends Controller
{
    public function index(): JsonResponse
    {
        $tables = PubTable::latest()->get();

        return response()->json([
            'message' => 'Pub table list',
            'data' => $tables,
        ]);
    }

    public function store(StorePubTableRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['table_number'])) {
            $data['table_number'] = trim($data['table_number']);
        }

        $exists = PubTable::where('table_number', $data['table_number'])->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Table number already exists',
            ], 422);
        }

        $table = PubTable::create($data);

        return response()->json([
            'message' => 'Pub table created successfully',
            'data' => $table,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $table = PubTable::findOrFail($id);

        return response()->json([
            'message' => 'Pub table detail',
            'data' => $table,
        ]);
    }

    public function update(UpdatePubTableRequest $request, string $id): JsonResponse
    {
        $table = PubTable::findOrFail($id);
        $data = $request->validated();

        if (isset($data['table_number'])) {
            $data['table_number'] = trim($data['table_number']);

            $exists = PubTable::where('table_number', $data['table_number'])
                ->get()
                ->first(function ($item) use ($id) {
                    return (string) $item->_id !== (string) $id;
                });

            if ($exists) {
                return response()->json([
                    'message' => 'Table number already exists',
                ], 422);
            }
        }

        $table->update($data);

        return response()->json([
            'message' => 'Pub table updated successfully',
            'data' => $table->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $table = PubTable::findOrFail($id);
        $table->delete();

        return response()->json([
            'message' => 'Pub table deleted successfully',
        ]);
    }
}