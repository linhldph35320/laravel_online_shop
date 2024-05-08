<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandsController extends Controller
{
    public function index(Request $request){
        $brands = Brand::latest('id');

        if (!empty($request->get('keyword'))) {
            $brands = Brand::where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $brands = $brands->paginate(10);

        $data['brands'] = $brands;

        return view('admin.brands.list',$data);
    }

    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands',
            'status' => 'required'
        ]);

        if($validator->passes()){

            $brand = new Brand();

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;

            $brand->save();

            session()->flash('success','Brand added successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Brand added successfully.'
            ]);
        }else{
            return response()->json([
                'status'=> false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id){
        $brand = Brand::find($id);

        if(empty($brand)){
            session()->flash('error','Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $data['brand'] = $brand;

        return view('admin.brands.edit',$data);
    }

    public function update($id, Request $request){
        $brand = Brand::find($id);

        if(empty($brand)){
            session()->flash('error','Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.'id',
            'status' => 'required'
        ]);

        if($validator->passes()){

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;

            $brand->save();

            session()->flash('success','Brand updated successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Brand updated successfully.'
            ]);
        }else{
            return response()->json([
                'status'=> false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id){
        $brand = Brand::find($id);

        if(empty($brand)){
            session()->flash('error','Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $brand->delete();

        session()->flash('success','Brand deleted successfully.');

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully.'
        ]);
    }
}
