<?php

namespace App\Http\Controllers\Cart;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductGroupItem;
use App\Models\UserProductGroup;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected Cart $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,product_id',
        ]);
        return $this->cart->firstOrCreate(
            [
                'user_id' =>  $request->user()->id,
                'product_id' => $request->product_id
            ]
        );
    }

    public function set(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,product_id',
            'quantity'      => 'required|numeric'
        ]);
        
        
        $cart = $this->cart
        ->where('user_id', $request->user()->id)
        ->where('product_id', $request->product_id);
        return $cart->update(
            [
                'quantity'      =>  $request->quantity
            ]
        );
    }


    public function remove(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,product_id',
        ]);
        return $this->cart->where(
            [
                'user_id' =>  $request->user()->id,
            ]
        )->where('product_id', $request->product_id)->delete();
    }

    public function get()
    {
        $carts = $this->cart
        ->where('user_id', Auth::guard('api')->id())
        ->with('product')
        ->get();
        $products = [];
        foreach($carts as $cart) {
            $products[] = $cart->product_id;
        }
        $discount = $this->findDiscount($products);

        return [
            'products' => $carts->map(function ($carts) {
                return [
                    'product_id' => $carts->product_id,
                    'quantity' => $carts->quantity,
                    'price' => optional($carts->product)->price,
                ];
            }),
            'discount' => $discount,
        ];
    }

    public function findDiscount($products, ) {
        $discount = 0;
        $userProductGroup = UserProductGroup::where('user_id', Auth::guard('api')->id())->get();
        foreach($userProductGroup as $group) {
            $productGroupItem = ProductGroupItem::where('group_id', $group->group_id)->get();
            $groupProducts = [];
            foreach($productGroupItem as $groupItem) {
                $groupProducts[] = $groupItem->product_id;
            }
            if($this->InOrNot($groupProducts, $products)) {
                $discountedProduct = $this->cart
                ->where('user_id', Auth::guard('api')->id())
                ->whereIn('product_id', $groupProducts)
                ->with('product')
                ->get();
                if($discountedProduct) {
                    $minCount = $discountedProduct[0]['quantity'];
                    $sumPrise = 0;
                    foreach($discountedProduct as $prod) {
                        $minCount = $minCount > $prod['quantity'] ? $prod['quantity'] : $minCount;
                        $sumPrise += $prod->product->price;
                    }
                    $discount += ($minCount * $sumPrise * $group->discount / 100);
                }
            }
        }
        return $discount;
    }

    public function InOrNot($arr1, $arr2) {
        foreach($arr1 as $element) {
            if(!in_array($element, $arr2)) {
                return 0;
            }
        }
        return 1;
    }
}
