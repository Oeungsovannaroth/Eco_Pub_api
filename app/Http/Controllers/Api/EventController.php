<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::latest()->get()->map(function ($event) {
            $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
            return $event;
        });

        return response()->json([
            'message' => 'Event list',
            'data' => $events,
        ]);
    }

    public function active(): JsonResponse
    {
        $events = Event::whereIn('status', ['upcoming', '   '])
            ->latest()
            ->get()
            ->map(function ($event) {
                $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
                return $event;
            });

        return response()->json([
            'message' => 'Active event list',
            'data' => $events,
        ]);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($data);
        $event->image_url = $event->image ? asset('storage/' . $event->image) : null;

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $event->image_url = $event->image ? asset('storage/' . $event->image) : null;

        return response()->json([
            'message' => 'Event detail',
            'data' => $event,
        ]);
    }

    public function update(UpdateEventRequest $request, string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($event->image && Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }

            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($data);
        $event->image_url = $event->image ? asset('storage/' . $event->image) : null;

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }
}