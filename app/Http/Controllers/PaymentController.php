<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\DigitalCardPurchased;
use App\Models\CardCode;
use App\Models\PrivateInformation;

// require_once('vendor/autoload.php');


class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Payments appear successfully',
            'result' => Payment::all(),
            'error' => null,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'payment_method' => 'required',
            'amount' => 'required|numeric',
            'status' => 'required',
        ]);

        $res = Payment::create($request->post());

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'result' => $res,
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Faied to create a new payment',
            'result' => null,
            'error' => null,
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return response()->json([
            'success' => true,
            'message' => 'Payment appear successfully',
            'result' => $payment,
            'error' => null,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'payment_method' => 'required',
            'amount' => 'required|numeric',
            'status' => 'required',
        ]);

        $payment->fill($request->post())->update();
        $res = $payment->save();

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Payment udpated successfully',
                'result' => Payment::find($payment->id),
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Faied to udpate a new payment',
            'result' => null,
            'error' => null,
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $res = $payment->delete();
        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Payemnt deleted successfully',
                'result' => $payment,
                'error' => null,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Faied to delete the payemnt',
            'result' => null,
            'error' => null,
        ], 400);
    }

    public function callback(Request $request)
    {
        $input = $request->all();


        $sk = PrivateInformation::where('key', 'api_secret_key')->first();
        $sk = $sk->value;

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $sk"
        ];

        $ch = curl_init();
        $url = "https://api.tap.company/v2/charges/" . $input['tap_id'];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        $res = json_decode($output);
        if ($res->status === "CAPTURED") {
            $data["user_id"] = $res->customer->id;
            $data["order_id"] = $res->id;
            $data["amount"] = $res->amount;
            $data["status"] = $res->status;

            Payment::create($data);
        } else {
            $data["user_id"] = $res->customer->id;
            $data["order_id"] = $res->id;
            $data["amount"] = 0;
            $data["status"] = $res->status;
            Payment::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'resolved successfully',
            'result' => $res,
            'error' => null,
        ], 200);
    }

    public function check_out(User $user, Request $request)
    {
        $data['amount'] = $request->total_amount;
        $data['currency'] = $request->currency;
        $data['customer']['first_name'] = $user->full_name;
        $data['customer']['email'] = $user->email;
        $data['source']['id'] = 'src_card';
        $data['redirect']['url'] = route("callback_route");


        $sk = PrivateInformation::where('key', 'api_secret_key')->first();
        $sk = $sk->value;


        if ($sk) {
            $headers = [
                "Content-Type: application/json",
                "Authorization: Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ"
            ];

            $ch = curl_init();
            $url = "https://api.tap.company/v2/charges";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $output = curl_exec($ch);

            curl_close($ch);
            $res = json_decode($output);
            $url = $res->transaction->url;
            return response()->json([
                'success' => true,
                'message' => "Successfully",
                'result' => $url,
                'error' => null,
            ], 200);
            // return redirect()->to($url);
        } else {
            // $data["user_id"] = $user->id;
            // $data["order_id"] = $order->id;
            // $data["amount"] = $order->total_amount;
            // $data["status"] = "فشلت عملية الدفع";
            //
            // Payment::create($data);
            return response()->json([
                'success' => true,
                'message' => 'لقد أضيف الطلب، ولكن فشلت عملية الدفع',
                'result' => null,
                'error' => null,
            ], 400);
        }
    }

    public function store_order(Request $request)
    {

        // $client = new \GuzzleHttp\Client();
        // $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
        //     'body' => '{"amount":1,"currency":"KWD","customer_initiated":true,"threeDSecure":true,"save_card":false,"description":"Test Description","metadata":{"udf1":"Metadata 1"},"reference":{"transaction":"txn_01","order":"ord_01"},"receipt":{"email":true,"sms":true},"customer":{"first_name":"test","middle_name":"test","last_name":"test","email":"test@test.com","phone":{"country_code":965,"number":51234567}},"source":{"id":"src_all"},"post":{"url":"http://your_website.com/post_url"},"redirect":{"url":"http://your_website.com/redirect_url"}}',
        //     'headers' => [
        //         'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
        //         'accept' => 'application/json',
        //         'content-type' => 'application/json',
        //     ],
        // ]);

        // $request->validate([
        //     'account' => "required",
        // ]);

        try {
            // Retrieve the user based on the provided user_id
            $user = User::find($request->user_id);

            // Create the order
            $order = Order::create([
                "user_id" => $user->id,
                "total_amount" => $request->total_amount,
                "currency" => $request->currency,
                "status" => $request->status,
            ]);

            // Initialize an array to store product IDs and their corresponding quantities
            $updatedProductQuantities = [];

            // Map the order items and create them in bulk
            $orderItems = collect($request->orderItems)->map(function ($item) use ($order, &$updatedProductQuantities, &$user) {
                $productID = $item['id'];
                $quantity = $item['quantity'];

                // Update product quantity and store it in the array
                $updatedProductQuantities[$productID] = $quantity;

                //TODO:: should be when payment successful, send card code to user email.
                // if ($item['is_service'] !== 1) {
                //     $code = CardCode::where('product_id', $item->product_id)->first();

                //     Mail::to($user->email)->send(new DigitalCardPurchased($code));
                // }
                //------------------------------



                return [
                    "order_id" => $order->id,
                    "product_id" => $item['id'],
                    "quantity" => $item['quantity'],
                    "subtotal" => $order->currency === "QAR" ? $item['qar_price'] * $item['quantity'] : $item['usd_price'] * $item['quantity'],
                    "account" => $item['account'],
                ];
            });

            OrderItem::insert($orderItems->toArray());

            // Update product quantities
            foreach ($updatedProductQuantities as $productID => $quantity) {
                // Retrieve the product and update its quantity
                $product = Product::find($productID);
                $product->stock_quantity -= $quantity;
                $product->save();

                if ($product->stock_quantity <= 0) {
                    Notification::create([
                        'user_id' => $order->user_id,  // Set the user ID associated with the product
                        'title' => 'منتج قد نفذ',
                        'description' => "لقد قام " . $order->user->full_name . " بإنشاء طلب جديد على منتد قد نفذ، اسم المنتج: $product->product_name",
                        'is_read' => false,  // You can set this based on your application's logic
                    ]);
                } else if ($product->stock_quantity < 3) {
                    // Create a notification if stock quantity is less than 3
                    Notification::create([
                        'user_id' => $order->user_id,  // Set the user ID associated with the product
                        'title' => 'المنتج على وشك النفاذ',
                        'description' => "تبقى أقل من 3 قطع من هذا المنتج: $product->product_name",
                        'is_read' => false,  // You can set this based on your application's logic
                    ]);
                }
            }

            // Create a notification if stock quantity is less than 3
            Notification::create([
                'user_id' => $order->user_id,  // Set the user ID associated with the product
                'title' => 'طلب جديد',
                'description' => "لقد قام " . $order->user->full_name . " بإنشاء طلب جديد",
                'is_read' => false,  // You can set this based on your application's logic
            ]);


            return redirect()->route("check_out", ['user' => $order->user_id, "order" => $order->id]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'result' => $order,
                'error' => $e->getMessage(),
            ], 500); // You can change the HTTP status code as needed
        }
    }
}
