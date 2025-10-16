<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * User Registration
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
                'contact' => 'required|digits_between:10,21|unique:users,contact',

            ]);

            $data['password'] = bcrypt($data['password']);
            $user = User::create($data);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse('REGISTER_SUCCESS', [
                'token' => $token,
                'user'  => $user,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($credentials)) {
                return $this->errorResponse(ResponseCode::UNAUTHORIZED, 'INVALID_CREDENTIALS');
            }

            $user = Auth::user();

            if (!$user->is_active) {
                return $this->errorResponse(ResponseCode::FORBIDDEN, 'INACTIVE_USER');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse('LOGIN_SUCCESS', [
                'token' => $token,
                'user'  => $user,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return $this->successResponse('LOGOUT_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Forgot Password (Send OTP)
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            // Email sending (optional)
            // Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            //     $message->to($user->email)->subject('Password Reset OTP');
            // });

            return $this->successResponse('OTP_SENT', [
                'email' => $user->email,
                'otp'   => $otp, 
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp'   => 'required'
            ]);

            $user = User::where('email', $request->email)
                ->where('otp', $request->otp)
                ->where('otp_expires_at', '>', Carbon::now())
                ->first();

            if (!$user) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'OTP_INVALID');
            }

            return $this->successResponse('SUCCESS', [
                'email' => $user->email,
                'otp_verified' => true
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required',
                'password' => 'required|confirmed|min:6'
            ]);

            $user = User::where('email', $request->email)
                ->where('otp', $request->otp)
                ->where('otp_expires_at', '>', Carbon::now())
                ->first();

            if (!$user) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'OTP_INVALID');
            }

            $user->password = bcrypt($request->password);
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            return $this->successResponse('PASSWORD_RESET_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
