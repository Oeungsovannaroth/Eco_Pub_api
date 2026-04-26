<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLedMessageRequest;
use App\Http\Requests\UpdateLedMessageRequest;
use App\Models\LedMessage;
use Illuminate\Http\JsonResponse;

class LedMessageController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = LedMessage::latest()->get();

        return response()->json([
            'message' => 'LED message list',
            'data' => $messages,
        ]);
    }

    public function active(): JsonResponse
    {
        $today = now()->toDateString();

        $messages = LedMessage::where('status', 'active')
            ->get()
            ->filter(function ($message) use ($today) {
                $startOk = !isset($message->start_date) || $message->start_date <= $today;
                $endOk = !isset($message->end_date) || $message->end_date >= $today;
                return $startOk && $endOk;
            })
            ->values();

        return response()->json([
            'message' => 'Active LED message list',
            'data' => $messages,
        ]);
    }

    public function store(StoreLedMessageRequest $request): JsonResponse
    {
        $message = LedMessage::create($request->validated());

        return response()->json([
            'message' => 'LED message created successfully',
            'data' => $message,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $message = LedMessage::findOrFail($id);

        return response()->json([
            'message' => 'LED message detail',
            'data' => $message,
        ]);
    }

    public function update(UpdateLedMessageRequest $request, string $id): JsonResponse
    {
        $message = LedMessage::findOrFail($id);
        $message->update($request->validated());

        return response()->json([
            'message' => 'LED message updated successfully',
            'data' => $message,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $message = LedMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'message' => 'LED message deleted successfully',
        ]);
    }
}