<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = MenuItem::with('category')->latest()->get()->map(function ($item) {
            $item->image_url = $item->image ? asset('storage/' . $item->image) : null;
            return $item;
        });

        return response()->json([
            'message' => 'Menu item list',
            'data' => $items,
        ]); 
    }

    public function publicIndex(): JsonResponse
    {
        $items = MenuItem::with('category')
            ->where('status', 'active')
            ->where('is_available', true)
            ->latest()
            ->get()
            ->map(function ($item) {
                $item->image_url = $item->image ? asset('storage/' . $item->image) : null;
                return $item;
            });

        return response()->json([
            'message' => 'Public menu item list',
            'data' => $items,
        ]);
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        $category = Category::find($data['category_id']);

        if (! $category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $item = MenuItem::create($data);
        $item->load('category');
        $item->image_url = $item->image ? asset('storage/' . $item->image) : null;

        return response()->json([
            'message' => 'Menu item created successfully',
            'data' => $item,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $item = MenuItem::with('category')->findOrFail($id);
        $item->image_url = $item->image ? asset('storage/' . $item->image) : null;

        return response()->json([
            'message' => 'Menu item detail',
            'data' => $item,
        ]);
    }

    public function update(UpdateMenuItemRequest $request, string $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);
        $data = $request->validated();

        if (isset($data['category_id'])) {
            $category = Category::find($data['category_id']);

            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }
        }

        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $item->update($data);
        $item->load('category');
        $item->image_url = $item->image ? asset('storage/' . $item->image) : null;

        return response()->json([
            'message' => 'Menu item updated successfully',
            'data' => $item,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);

        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully',
        ]);
    }
}
