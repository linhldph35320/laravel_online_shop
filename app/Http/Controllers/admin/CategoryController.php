<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest();

        if (!empty($request->get('keyword'))) {
            $categories = Category::where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $categories = $categories->paginate(10);

        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            // Lưu hình ảnh ở đây
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);

                $extArray = explode('.', $tempImage->name);

                $ext = last($extArray); // hàm last để lấy giá trị cuối trong mảng

                $newImageName = $category->id . '.' . $ext;

                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;

                File::copy($sPath, $dPath);

                // Tạo thumb
                $sourcePath = public_path('/uploads/category/' . $newImageName);
                $dPath = public_path() . '/uploads/category/thumb/' . $newImageName;
                $manager = new ImageManager(new Driver());
                $image = $manager->read($sourcePath);
                $image->cover(450,600);
                $image->save($dPath);

                $category->image = $newImageName;
                $category->save();
            }

            session()->flash('success', 'Category added successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($categoryId, Request $request)
    {
        $category = Category::find($categoryId);

        if (empty($category)) {
            return redirect()->route('categories.index');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update($categoryId, Request $request)
    {
        $category = Category::find($categoryId);

        if (empty($category)) {
            session()->flash('error','Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
        ]);

        if ($validator->passes()) {

            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            $oldImage = $category->image;

            // Lưu hình ảnh ở đây
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);

                $extArray = explode('.', $tempImage->name);

                $ext = last($extArray); // hàm last để lấy giá trị cuối trong mảng

                $newImageName = $category->id.'-'.time(). '.' . $ext;

                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;

                File::copy($sPath, $dPath);

                // Tạo thumb
                $sourcePath = public_path('/uploads/category/' . $newImageName);
                $dPath = public_path() . '/uploads/category/thumb/' . $newImageName;
                $manager = new ImageManager(new Driver());
                $image = $manager->read($sourcePath);
                // $image->resize(450, 600);
                $image->cover(450,600);
                $image->save($dPath);
                $category->image = $newImageName;
                $category->save();

                // Xóa ảnh cũ khi update
                File::delete(public_path().'/uploads/category/thumb/'.$oldImage);
                File::delete(public_path().'/uploads/category/'.$oldImage);
            }

            session()->flash('success', 'Category updated successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($categoryId, Request $request)
    {
        $category = Category::find($categoryId);

        if(empty($category)){
            session()->flash('error','Category not found');
            return response()->json([
                'status'=>true,
                'message' => 'Category not found'
            ]);
            // return redirect()->route('categories.index');
        }

        File::delete(public_path().'/uploads/category/thumb/'.$category->image);
        File::delete(public_path().'/uploads/category/'.$category->image);

        $category->delete();

        session()->flash('success','Category deleted successfully.');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }
}
