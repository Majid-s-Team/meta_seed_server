<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Authenticated User Profile
     */
    public function view()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            return $this->successResponse('SUCCESS', $user);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Update User Profile
     */
  public function update(Request $request)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            // 'contact' => 'sometimes|digits_between:10,21|unique:users,contact,' . $user->id,
           'contact' => [
                'required',
                'regex:/^(\+?\d{1,3}[-\s]?)?[\d\s-]{7,20}$/',
                'unique:users,contact',
            ],
            'image' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FAILED', $validator->errors());
        }

        $data = $validator->validated();

        // ✅ Image will now properly update
        if (isset($data['image'])) {
            $data['image'] = $data['image'];
        }

        $user->update($data);

        $user->image = $user->image ? url($user->image) : null;

        return $this->successResponse('SUCCESS', $user);

    } catch (\Exception $e) {
        return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
    }
}



    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FAILED', $validator->errors());
            }

            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'FAILED', [
                    'message' => 'Current password is incorrect',
                ]);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return $this->successResponse('SUCCESS', ['message' => 'Password updated successfully']);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Toggle User Active/Inactive Status (Admin Only)
     */
    public function toggleActive($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            $user->is_active = !$user->is_active;
            $user->save();

            return $this->successResponse('SUCCESS', [
                'user_id' => $user->id,
                'is_active' => $user->is_active,
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }
    public function uploadMedia(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'image' => 'required|image|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FAILED', $validator->errors());
            }

            // Store the file
            $path = $request->file('image')->store('uploads', 'public');

            $url = asset('storage/' . $path);

            return $this->successResponse('SUCCESS', [
                'url' => $url,
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }
}
