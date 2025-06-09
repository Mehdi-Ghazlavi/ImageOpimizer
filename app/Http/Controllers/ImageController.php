<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    public function optimizeImage(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|max:10240', // 10MB limit
        ]);

        $results = [];

        $imageManager = new ImageManager(new Driver());
        $destination = public_path('optimized');

        // Ensure the directory exists
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        foreach ($request->file('images') as $file) {
            try {
                $originalSize = $file->getSize();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = uniqid($originalName . '_') . '.webp';

                $filePath = $destination . '/' . $filename;

                // Optimize and convert to WebP
                $imageManager->read($file)
                    ->toWebp(80)
                    ->save($filePath);

                $optimizedSize = filesize($filePath);

                $results[] = [
                    'name' => $filename,
                    'url' => asset("optimized/{$filename}"),
                    'originalSize' => $originalSize,
                    'optimizedSize' => $optimizedSize,
                    'savings' => round((1 - ($optimizedSize / $originalSize)) * 100)
                ];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image optimization failed: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'images' => $results,
        ]);
    }
}
