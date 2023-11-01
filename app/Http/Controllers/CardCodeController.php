<?php

namespace App\Http\Controllers;

use App\Models\CardCode;
use App\Models\Category;
use App\Models\Product;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;

class CardCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'All codes appear successfully',
            'result' => CardCode::all(),
            'error' => null,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'card_code' => 'required',
        ]);

        // $catProduct = CategoryProduct::where("product_id", $request->product_id)->first();

        // if ($catProduct === null) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to create the code.',
        //         'result' => null,
        //         'error' => 'Product not found.',
        //     ], 404);
        // }

        // $cat = Category::find($catProduct->category_id);

        // if ($cat->is_service !== 0) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'This product is not a digital card',
        //         'result' => null,
        //         'error' => "this product is not a digital card",
        //     ], 400);
        // }
        $product = Product::with('card_codes')->find($request->product_id);

        if ($product) {
            $validCardCodeCount = $product->card_codes->where('is_valid', true)->count();
            if ($product->stock_quantity <= $validCardCodeCount) {

                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إضافة كود آخر، قم بتحديث كمية المنتج لإضافة كود جديد',
                    'result' => null,
                    'error' => null,
                ], 200);
            }
        }

        $res = CardCode::create($request->post());

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Card Code created successfully',
                'result' => $res,
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create the code.',
            'result' => null,
            'error' => "There was an error while creating the code",
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(CardCode $cardCode)
    {
        return response()->json([
            "success" => true,
            "message" => "Get Code successfully",
            "result" => $cardCode,
            "error" => null
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function showProductOfCard(CardCode $cardCode)
    {
        return response()->json([
            "success" => true,
            "message" => "Get Product successfully",
            "result" => $cardCode->product,
            "error" => null
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CardCode $cardCode)
    {
        $request->validate([
            'product_id' => 'required',
            'card_code' => 'required',
        ]);

        $cardCode->fill($request->post())->update();
        $res = $cardCode->save();


        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Card Code updated successfully',
                'result' => CardCode::find($cardCode->id),
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to updated the code.',
            'result' => null,
            'error' => "There was an error while updating the code",
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CardCode $cardCode)
    {
        $res = $cardCode->delete();

        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "تم حذف الكود بنجاح",
                "result" => $cardCode,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "فشلت عملية الحذف",
            "result" => null,
            "error" => null
        ], 400);
    }
}
