<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Orders appear successfully',
            'result' => Order::with(["order_items", "user"])->orderBy("created_at", "desc")->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function indexWithTrashed()
    {
        return response()->json([
            'success' => true,
            'message' => 'Orders appear successfully',
            'result' => Order::withTrashed()->orderBy("created_at", "desc")->load(["order_items"])->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'total_amount' => 'required|numeric',
            'status' => 'required',
        ]);


        $res = Order::create($request->post());



        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'result' => $res,
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create the Order.',
            'result' => null,
            'error' => "There was an error while creating the Order",
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return response()->json([
            'success' => true,
            'message' => 'Get Order successfully',
            'result' => $order->load(['order_items.product.reviews', "payment", 'user']),
            'error' => null,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required',
        ]);

        $order->fill($request->post())->update();
        $res = $order->save();

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'result' => Order::with('order_items.product')->find($order->id),
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to updated the Order.',
            'result' => null,
            'error' => "There was an error while updated the Order",
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $error = null;
        foreach ($order->order_items as $item) {
            $delete_res = $item->delete();
            if (!$delete_res) {
                $error = "there was an error while deleting the order item";
            }
        }

        if ($error === null) {
            $res = $order->delete();

            if ($res) {
                return response()->json([
                    "success" => true,
                    "message" => "Order deleted successfully",
                    "result" => $order,
                    "error" => null
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "Fail to delete the Order",
                "result" => null,
                "error" => null
            ], 400);
        } else {
            return response()->json([
                "success" => true,
                "message" => "Fail to delete the Order",
                "result" => null,
                "error" => $error
            ], 400);
        }
    }

    public function destroyOrders(Request $request)
    {
        $orderIds = $request->input('orderIds');
        $error = null;

        foreach ($orderIds as $id) {
            $orders_will_delete = Order::find($id);

            if (!$orders_will_delete) {
                // Handle the case where the user is not found
                $error = response()->json([
                    "success" => false,
                    "message" => "لم يتم العثور على الطلب",
                    "result" => null,
                    "error" => "User not found"
                ], 404);
            } else {

                $res = $orders_will_delete->delete(); // Soft delete
                if (!$res) {
                    $error = response()->json([
                        "success" => false,
                        "message" => "فشلت عملية الحذف",
                        "result" => null,
                        "error" => "Failed to soft delete user"
                    ], 400);
                }
            }
        }

        if ($error !== null) {
            return $error;
        }

        return response()->json([
            "success" => true,
            "message" => "تم حذف الطلبات المحددة بنجاح",
            "result" => null,
            "error" => null
        ], 200);
    }
}
