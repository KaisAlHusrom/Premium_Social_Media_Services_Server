<?php

namespace App\Http\Controllers;

use App\Mail\ForgetPassword;
use Illuminate\Http\Request;
// use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordController extends Controller
{


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => "required|email",
            'password' => "required|min:6|confirmed",
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "الإيميل غير موجود",
                "result" => null,
                "error" => "الإيميل غير موجود"
            ], 400); // Handle the case when the user doesn't exist
        }

        $customToken = bin2hex(random_bytes(32));

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'token' => $customToken,
            'created_at' => Carbon::now()
        ]);

        Mail::to($request->email)->send(new ForgetPassword(route('reset-link', ['token' => $customToken])));

        return response()->json([
            "success" => true,
            "message" => "لقد تم إرسال رسالة تأكيد إلى إيميلك الشخصي",
            "error" => null
        ], 200);
    }

    public function resetPasswordAfterAuthentication($token)
    {
        // Find the user associated with the provided token
        $passwordResetToken = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        // Check if the token exists and is valid
        if (!$passwordResetToken || strtotime($passwordResetToken->created_at) < strtotime('-1 hour')) {
            return redirect()->to('https://itsnology.com/login'); // Redirect to a password reset form with an error message
        }

        // Use the retrieved user email to reset the password
        $user = User::where('email', $passwordResetToken->email)->first();

        $new_password = $passwordResetToken->password;
        // Reset the user's password here
        $user->update([
            'password' => $new_password, // Replace 'new_password' with the actual new password
        ]);

        // Remove the used token from the database to ensure it can't be reused
        DB::table('password_reset_tokens')->where('token', $token)->delete();

        // Redirect to a password reset success page or login page
        return redirect()->to('https://itsnology.com/login');
    }
}
