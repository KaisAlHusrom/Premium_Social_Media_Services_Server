<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orderItems = OrderItem::with('order', 'product')->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders appear successfully',
            'result' => $orderItems,
            'error' => null,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            'quantity' => 'required|numeric',
            'subtotal' => 'required|numeric',
            "account_name",
            "link",
        ]);

        $res = OrderItem::create($request->post());

        $result = [
            'orderItem' => $res,
            'product' => $res->product,
            'order' => $res->order,
        ];

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Order Item created successfully',
                "result" => $result,
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create the Order Item.',
            'result' => null,
            'error' => "There was an error while creating the Order Item",
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem)
    {
        $result = [
            'orderItem' => $orderItem,
            'product' => $orderItem->product,
            'order' => $orderItem->order,
        ];

        return response()->json([
            "success" => true,
            "message" => "Get Order successfully",
            "result" => $result,
            "error" => null
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderItem $orderItem)
    {
        $request->validate([
            'order_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            'quantity' => 'required|numeric',
            'subtotal' => 'required|numeric',
        ]);

        $orderItem->fill($request->post())->update();
        $res = $orderItem->save();

        $newOrderItem = OrderItem::find($orderItem->id);

        $result = [
            'orderItem' => $newOrderItem,
            'product' => $newOrderItem->product,
            'order' => $newOrderItem->order,
        ];

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Order Item updated successfully',
                'result' => $result,
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update the Order Item.',
            'result' => null,
            'error' => "There was an error while creating the Order Item",
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem)
    {
        $result = [
            'orderItem' => $orderItem,
            'product' => $orderItem->product,
            'order' => $orderItem->order,
        ];
        $res = $orderItem->delete();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "Order deleted successfully",
                "result" => $result,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "Fail to delete the Order",
            "result" => null,
            "error" => null
        ], 400);
    }
}
