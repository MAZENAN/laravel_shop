<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $key = $request->input('search', '');
        $order = $request->input('order', '');
        $productBuilder = Product::query()->where('on_sale', true);

        if ($key) {
            $search = '%' . $key . '%';
            $productBuilder->where('title', 'like', $search);

            //关联模型包含
            $productBuilder->orWhereHas('skus', function ($query) use ($search) {
                $query->where('title', 'like', $search);
            });
        }

        if ($order && (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) && in_array($m[1], ['price', 'sold_count', 'rating'])) {
            $productBuilder->orderBy($m[1], $m[2]);
        }

        $products = $productBuilder->paginate(4);

        return view('product.index', [
            'products' => $products,
            'filters' => [
                'search' => $key,
                'order'  => $order
            ]
        ]);
    }

    public function show(Request $request, Product $product)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;
        if ($user = $request->user()) {
            $favored = boolval($user->favoriteProducts->find($product->id));
        }

        return view('product.show',['product' => $product, 'favored' => $favored]);
    }

    /**
     * 收藏
     *
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }


    /**
     * 取消收藏
     *
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }
}
