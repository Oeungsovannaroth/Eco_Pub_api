<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::latest()->get()->map(function ($banner) {
            $banner->image_url = $banner->image ? asset('storage/' . $banner->image) : null;
            return $banner;
        });

        return response()->json([
            'message' => 'Banner list',
            'data' => $banners,
        ]);
    }

    public function active(): JsonResponse
    {
        $banners = Banner::where('status', 'active')
            ->latest()
            ->get()
            ->map(function ($banner) {
                $banner->image_url = $banner->image ? asset('storage/' . $banner->image) : null;
                return $banner;
            });

        return response()->json([
            'message' => 'Active banner list',
            'data' => $banners,
        ]);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner = Banner::create($data);
        $banner->image_url = $banner->image ? asset('storage/' . $banner->image) : null;

        return response()->json([
            'message' => 'Banner created successfully',
            'data' => $banner,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $banner->image_url = $banner->image ? asset('storage/' . $banner->image) : null;

        return response()->json([
            'message' => 'Banner detail',
            'data' => $banner,
        ]);
    }

    public function update(UpdateBannerRequest $request, string $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }

            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);
        $banner->image_url = $banner->image ? asset('storage/' . $banner->image) : null;

        return response()->json([
            'message' => 'Banner updated successfully',
            'data' => $banner,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);

        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return response()->json([
            'message' => 'Banner deleted successfully',
        ]);
    }
}