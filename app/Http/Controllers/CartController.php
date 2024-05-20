<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $product = Product::with('product_images')->find($request->id);

        if($product == null){
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ]);
        }

        if(Cart::count() > 0){
            // echo "Product already in cart";
            // Sản phẩm được tìm thấy trong giỏ hàng
            // Kiểm tra xem sản phẩm có tồn tại trong giỏ hàng hay không
            // Trả lại thông báo khi sản phẩm đó đã tồn tại trong giỏ hàng
            // Nếu sản phẩm đó không tồn tại trong giỏ hàng, thêm sản phẩm đó vào giỏ

            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if($item->id == $product->id){
                    $productAlreadyExist = true;
                }
            }

            if($productAlreadyExist == false){
                Cart::add($product->id, $product->title, 1, $product->price,
                ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '' ]);

                $status = true;
                $message = $product->title.' added in cart';
            }else{
                $status = false;
                $message = $product->title.' already added in cart';
            }

        } else{
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '' ]);
            $status = true;
            $message = $product->title.' added in cart';
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function cart(){
        $cartContent = Cart::content();
        $data['cartContent'] = $cartContent;
        return view('front.cart',$data);
    }
}
