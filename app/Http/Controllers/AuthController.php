<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $error = null;
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);


        if ($error === null) {
            $user = User::create($request->post(), ["password" => Hash::make($request->password)]);
            if ($user) {
                // Send email verification link
                $user->sendEmailVerificationNotification();


                // Create a notification if stock quantity is less than 3
                Notification::create([
                    'user_id' => $user->id,  // Set the user ID associated with the product
                    'title' => 'تسجيل مستخدم جديد',
                    'description' => "لقد قام $user->full_name بالتسجيل في الموقع",
                    'is_read' => false,  // You can set this based on your application's logic
                ]);

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

    public function signIn(Request $request)
    {
        $error = null;
        $request->validate([
            "email" => 'email|required',
            "password" => 'required'
        ]);

        $credentials = request(['email', 'password']);





        $user = User::where("email", $request->email)
            ->with("orders.order_items.product", "orders.payment")
            ->first();

        if (!$user) {
            $error = "المستخدم غير موجود"; // Handle the case when the user doesn't exist
        } elseif (!auth()->attempt($credentials)) {
            $error = "اسم المستخدم أو كلمة المرور خاطئة";
        } elseif ($user->email_verified_at === null) {
            $error = "عليك تأكيد التسجيل من خلال الرسالة التي أرسلت إلى بريدك الإلكتروني";
        }

        if ($error !== null) {
            return response()->json([
                "success" => false,
                "message" => $error,
                "result" => null,
                "error" => $error
            ], 400);
        }

        // Create a new access token
        $token = $user->createToken('auth-token', [], now()->addDay());

        // Set the is_validated column to true
        // $token->is_validated = true;
        // $token->save();

        // // Now, update the token to set is_validated to true using a raw SQL query
        // DB::update('UPDATE personal_access_tokens SET is_validated = 1 WHERE id = ?', [$token->id]);



        $auth_token = $token->plainTextToken;


        return response()->json([
            "success" => true,
            "message" => "Sign In Successful",
            "token" => $auth_token,
            "user" => $user,
            "error" => null
        ], 200);
    }


    public function SignOut(Request $request)
    {
        $current_user = $request->user();
        $user = User::find($current_user->currentAccessToken()->tokenable_id);
        // Retrieve the last validated token
        // $lastValidatedToken = PersonalAccessToken::where('tokenable_id', $user->id)
        //     // ->where('tokenable_type', get_class($user))
        //     // ->where('is_validated', true)
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // if ($lastValidatedToken) {
        //     // Set the `is_validated` flag to false
        //     $lastValidatedToken->is_validated = false;
        //     $lastValidatedToken->save();
        // }

        // Delete the last validated token (if it exists)
        // if ($lastValidatedToken) {
        //     $lastValidatedToken->delete();
        // }

        // Revoke the user's current token
        $token = PersonalAccessToken::where('tokenable_id', $user->id);
        $token->delete();

        return response()->json([
            "success" => true,
            "message" => "User sign out successfully",
            "result" => null,
            "error" => null
        ], 200);
    }

    public function checkToken(Request $request)
    {
        $current_user = $request->user();

        // $lastValidatedToken = PersonalAccessToken::where('tokenable_id', $user->id)
        //     // ->where('tokenable_type', get_class($user))
        //     // ->where('is_validated', true)
        //     ->orderBy('created_at', 'desc')
        //     ->first();
        // Authenticate the request using the "api" guard
        // Check if the token has expired

        if ($current_user->currentAccessToken()->expires_at < now()) {
            return response()->json([
                "success" => false,
                "message" => "Token has expired",
                "result" => null,
                "error" => "Token has expired"
            ], 400);
        }

        $user = User::with('orders.order_items.product', 'orders.payment')
            ->find($current_user->currentAccessToken()->tokenable_id);
        if ($user) {
            return response()->json([
                "success" => true,
                "message" => "Token is valid",
                "result" => $user,
                "error" => null
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Token is invalid",
                "result" => null,
                "error" => "Token is invalid"
            ], 400);
        }
    }
}
