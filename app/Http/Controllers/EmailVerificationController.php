<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;

class EmailVerificationController extends Controller
{




    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect()->to('login'); // Redirect to the login page
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->to('https://itsnology.com/login');
    }

    public function resend(Request $request)
    {
        // Resend email verification logic here
    }
}
