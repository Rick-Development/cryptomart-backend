<?php

namespace App\Http\Controllers\Admin;

use App\Models\Image;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }
        // ✅ Validate file
        // $validated = $request->validate([
        //     'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        // ]);

        // ✅ Store image
        $filename = Str::uuid() . '.' . $request->file('image')->getClientOriginalExtension();
        $path = $request->file('image')->storeAs('uploads', $filename, 'public');
        $url = asset('storage/' . $path);

        // ✅ Save record in database
        $image = Image::create([
            'filename' => $filename,
            'path' => $path,
            'url' => $url,
        ]);

        // ✅ Return JSON response
        return response()->json([
            'message' => 'Image uploaded successfully.',
            'data' => $image,
        ], 201);
    }
}