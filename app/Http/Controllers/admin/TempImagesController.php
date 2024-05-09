<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImagesController extends Controller
{
    public function create(Request $request)
    {
        $image = $request->image;

        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();

            $tempImage = new TempImage();
            $tempImage->name = 'NULL';
            $tempImage->save();

            $imageName = $tempImage->id.'.'.$ext;

            $tempImage->name = $imageName;
            $tempImage->save();

            $image->move(public_path() . '/temp', $imageName);

            // Tạo thumbnail
            $sourcePath = public_path('/temp/' . $imageName);
            $destPath = public_path('/temp/thumb/' . $imageName);
            $manager = new ImageManager(new Driver());
            $img = $manager->read($sourcePath);
            $img->cover(300, 275);
            $img->save($destPath);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'ImagePath' => asset('/temp/thumb/'.$imageName),
                'message' => 'Image uploaded successfully.'
            ]);
        }
    }
}
