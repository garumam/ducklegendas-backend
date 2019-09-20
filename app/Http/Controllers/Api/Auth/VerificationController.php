<?php

namespace App\Http\Controllers\Api\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    use VerifiesEmails;
    /**
    * Show the email verification notice.
    *
    */
    public function show()
    {
    //
    }
    /**
    * Mark the authenticated user’s email address as verified.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function verify(Request $request) {
        $userID = $request["id"];
        $user = User::findOrFail($userID);
        $date = date("Y-m-d g:i:s");
        $user->email_verified_at = $date; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
        $user->save();
        return response()->json("Email verified!");
    }
    /**
    * Resend the email verification notification.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function resend(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user){
            if ($user->hasVerifiedEmail()) {
                return response()->json("User already have verified email!", 422);
            }
            $user->sendApiEmailVerificationNotification();
            return response()->json("The notification has been resubmitted");
        }
        return response()->json("E-mail não cadastrado!",401);
    }
    
    public function notice(Request $request)
    {
        return response()->json("Verifique seu e-mail antes de acessar esta página!",401);
    }

}
