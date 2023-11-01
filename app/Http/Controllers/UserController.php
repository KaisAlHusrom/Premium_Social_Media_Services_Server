<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users appear successfully',
            'result' => User::withTrashed()->orderBy('created_at', 'desc')->get(),
            'error' => null,
        ], 200)->header('Accept', 'application/json');
    }

    /**
     * showWithOutTrashed
     */
    public function indexWithOutTrashed()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users appear successfully',
            'result' => User::orderBy('created_at', 'desc')
                ->get(),
            'error' => null,
        ], 200)->header('Accept', 'application/json');
    }

    /**
     * Display only trashed.
     */
    public function indexTrashed()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users appear successfully',
            'result' =>  User::onlyTrashed()->get(),
            'error' => null,
        ], 200)->header('Accept', 'application/json');
    }

    /**
     * Display only admins.
     */
    public function indexAdmins()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users appear successfully',
            'result' =>  User::where("is_admin", "=", 1)->get(),
            'error' => null,
        ], 200)->header('Accept', 'application/json');
    }

    //Search Users

    public function searchUsers(Request $request)
    {
        $searchTerm = $request->input('searchTerm');

        // Use the where method to filter users by the full_name attribute
        $users = User::where('full_name', 'like', "%$searchTerm%")->get();

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'result' => $users,
            'error' => null,
        ], 200);
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $error = null;
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);


        if ($error === null) {
            $user = User::create($request->post(), ["password" => Hash::make($request->password)]);
            if ($user) {
                // Send email verification link
                // $user->sendEmailVerificationNotification();
                return response()->json([
                    "success" => true,
                    "message" => "تم تسجيل المستخدم بنجاح. الرجاء فحص بريدك الإلكتروني لتأكيد التسجيل",
                    "result" => $user,
                    "error" => null
                ], 201);
            }
        }

        return response()->json([
            "success" => false,
            "message" => "فشل التسجيل، يرجى معاودة المحاولة لاحقا.",
            "result" => null,
            "error" => $error
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // The $user parameter is already resolved due to implicit model binding
        if ($user) {
            $user->load(['reviews.product', 'orders', 'payments']); // Eager load relationships
            return response()->json([
                "success" => true,
                "message" => "Get user successfully",
                "result" => $user,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "No user found",
            "result" => null,
            "error" => null
        ], 404);
    }

    /**
     * Display all reviews.
     */
    public function showReviews(User $user)
    {
        if ($user) {
            return response()->json([
                "success" => true,
                "message" => "Get user reviews",
                "result" => $user->reviews,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "No user found",
            "result" => null,
            "error" => null
        ], 404);
    }

    /**
     * Display all reviews.
     */
    public function showOrders(User $user)
    {
        if ($user) {
            return response()->json([
                "success" => true,
                "message" => "Get user orders",
                "result" => $user->orders,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "No user found",
            "result" => null,
            "error" => null
        ], 404);
    }

    /**
     * Display all reviews.
     */
    public function showPayments(User $user)
    {
        if ($user) {
            return response()->json([
                "success" => true,
                "message" => "Get user payments",
                "result" => $user->payments,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "No user found",
            "result" => null,
            "error" => null
        ], 404);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email',
        ]);

        $user->fill($request->post())->update();

        $res = $user->save();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "User updated successfully",
                "result" => $user,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "User update failed",
            "result" => null,
            "error" => null
        ], 400);
    }

    //change password
    public function updatePassword(Request $request, User $user)
    {
        // Validate the request
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // Check if the old password matches the user's current password
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "كلمة المرور القديمة خاطئة",
                "result" => null,
                "error" => null
            ], 400);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "تم تغيير كلمة المرور بنجاح",
            "result" => null,
            "error" => null
        ], 200);
    }

    /**
     * Update the specified resource to admin.
     */
    public function updateToAdmin(Request $request)
    {
        $user_ids = $request->input('user_ids');
        $error = null;


        foreach ($user_ids as $id) {
            $user_will_update = User::find($id);

            if (!$user_will_update) {
                // Handle the case where the user is not found
                $error = response()->json([
                    "success" => false,
                    "message" => "لم يتم العثور على العميل",
                    "result" => null,
                    "error" => "User not found"
                ], 404);
            } else {
                if ($user_will_update->id === 1) {
                    $error = response()->json([
                        "success" => false,
                        "message" => "لا يمكنك تغيير المدير الرئيسي للمتجر",
                        "result" => null,
                        "error" => "Failed to soft delete user"
                    ], 400);
                } else {
                    $user_will_update->is_admin = 1;
                    $res = $user_will_update->save();
                    if (!$res) {
                        $error = response()->json([
                            "success" => false,
                            "message" => "فشلت عملية التحديث",
                            "result" => null,
                            "error" => "Failed to soft delete user"
                        ], 400);
                    }
                }
            }
        }

        if ($error !== null) {
            return $error;
        }

        return response()->json([
            "success" => true,
            "message" => "تم تحديث العملاء الحددين بنجاح",
            "result" => null,
            "error" => null
        ], 200);
    }

    /**
     * Update the specified resource to admin.
     */
    public function updateToCustomer(Request $request)
    {
        $user_ids = $request->input('user_ids');
        $error = null;


        foreach ($user_ids as $id) {
            $user_will_update = User::find($id);

            if (!$user_will_update) {
                // Handle the case where the user is not found
                $error = response()->json([
                    "success" => false,
                    "message" => "لم يتم العثور على العميل",
                    "result" => null,
                    "error" => "User not found"
                ], 404);
            } else {
                if ($user_will_update->id === 1) {
                    $error = response()->json([
                        "success" => false,
                        "message" => "لا يمكنك تغيير المدير الرئيسي للمتجر",
                        "result" => null,
                        "error" => "Failed to soft delete user"
                    ], 400);
                } else {
                    $user_will_update->is_admin = 0;
                    $res = $user_will_update->save();
                    if (!$res) {
                        $error = response()->json([
                            "success" => false,
                            "message" => "فشلت عملية التحديث",
                            "result" => null,
                            "error" => "Failed to soft delete user"
                        ], 400);
                    }
                }
            }
        }

        if ($error !== null) {
            return $error;
        }

        return response()->json([
            "success" => true,
            "message" => "تم تحديث العملاء الحددين بنجاح",
            "result" => null,
            "error" => null
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $res = $user->delete();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "User deleted successfully",
                "result" => $user,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "User deleted failed",
            "result" => null,
            "error" => null
        ], 400);
    }

    /**
     * Remove array of users from the database
     */

    public function destroyUsers(Request $request)
    {
        $user_ids = $request->input('user_ids');
        $error = null;

        foreach ($user_ids as $id) {
            $user_will_delete = User::find($id);

            if (!$user_will_delete) {
                // Handle the case where the user is not found
                $error = response()->json([
                    "success" => false,
                    "message" => "لم يتم العثور على العميل",
                    "result" => null,
                    "error" => "User not found"
                ], 404);
            } else {
                if ($user_will_delete->id === 1) {
                    $error = response()->json([
                        "success" => false,
                        "message" => "لا يمكنك حذف المدير الرئيسي للمتجر",
                        "result" => null,
                        "error" => "Failed to soft delete user"
                    ], 400);
                } else {
                    $res = $user_will_delete->delete(); // Soft delete
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
        }

        if ($error !== null) {
            return $error;
        }

        return response()->json([
            "success" => true,
            "message" => "تم حذف العملاء الحددين بنجاح",
            "result" => null,
            "error" => null
        ], 200);
    }

    //get user data by token
    public function getUserProfile()
    {
        $user = auth()->user();

        return response()->json([
            "success" => true,
            "message" => "Get User info successfully",
            "result" => $user,
            "error" => null
        ], 200);
    }


    //get statics
    public function getStatics()
    {
        $usersCount = User::count();
        $productsCount = Product::count();
        $ordersCount = Order::count();
        $profit = Payment::sum('amount');

        $statics = [
            "salary" => $ordersCount,
            "customers" => $usersCount,
            "products" => $productsCount,
            "profit" => $profit
        ];

        return response()->json([
            "success" => true,
            "message" => "Get Statics info successfully",
            "result" => $statics,
            "error" => null
        ], 200);
    }
}
