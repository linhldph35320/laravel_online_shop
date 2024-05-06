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
    public function index(Request $request){
        $categories = Category::latest();

        if(!empty($request->get('keyword'))){
            $categories = Category::where('name','like','%'.$request->get('keyword').'%');
        }

        $categories = $categories->paginate(10);

        return view('admin.category.list',compact('categories'));
    }

    public function create(){
        return view('admin.category.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if($validator->passes()){

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            // Lưu hình ảnh ở đây
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);

                $extArray = explode('.',$tempImage->name);

                $ext = last($extArray); // hàm last để lấy giá trị cuối trong mảng

                $newImageName = $category->id.'.'.$ext;

                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;

                File::copy($sPath,$dPath);

                // Tạo thumb
                $sourcePath = public_path('/uploads/category/'.$newImageName);
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $manager = new ImageManager(new Driver());
                $image = $manager->read($sourcePath);
                $image->resize(450,600);
                $image->save($dPath);

                $category->image = $newImageName;
                $category->save();
            }

            session()->flash('success','Category added successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully.'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit(){

    }

    public function update(){

    }

    public function destroy(){

    }
}
