<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $subCategories = SubCategory::latest('id')->with('Category');

        if (!empty($request->get('keyword'))) {
            $subCategories = SubCategory::where('name', 'like', '%' . $request->get('keyword') . '%');
            $subCategories = SubCategory::with('Category')->where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $subCategories = $subCategories->paginate(10);

        return view('admin.sub_category.list', compact('subCategories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $sub_category = new SubCategory();
            $sub_category->name = $request->name;
            $sub_category->slug = $request->slug;
            $sub_category->status = $request->status;
            $sub_category->category_id = $request->category;
            $sub_category->save();

            session()->flash('success','Sub Category created successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Sub Category created successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
