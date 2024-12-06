<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        // Xác thực file upload
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        try {
            // Lưu ảnh vào thư mục 'uploads' trong 'storage/app/public/uploads'
            $path = $request->file('image')->store('uploads', 'public');

            // Trả về URL đầy đủ
            $url = Storage::url($path);

            return response()->json([
                'message' => 'Image uploaded successfully.',
                'url' => asset($url),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload image.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

