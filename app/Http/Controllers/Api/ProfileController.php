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

        if (isset($data['image'])) {
            $data['image'] = $data['image'];
        }

        $user->update($data);

        $user->image = $user->image ? url($user->image) : null;

        return $this->successResponse('PROFILE_UPDATED', $user);

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
                    'message' => 'Invalid current password.',
                ]);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return $this->successResponse('SUCCESS', ['message' => 'Your password has been changed successfully.']);

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
                'image' => 'required|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,heic|max:5120', // up to 5MB
            ], [
                'image.required' => 'Please upload an image file.',
                'image.mimes' => 'The uploaded file must be a valid image (jpeg, jpg, png, gif, webp, svg, bmp, tiff, heic).',
                'image.max' => 'The image size must not exceed 5MB.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'VALIDATION_FAILED', [
                    'errors' => $validator->errors()
                ]);
            }

            if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'INVALID_FILE', [
                    'error' => 'No valid image file uploaded or the file is corrupted.',
                ]);
            }

            $path = $request->file('image')->store('uploads', 'public');
            $url = asset('storage/' . $path);

            return $this->successResponse('UPLOAD_SUCCESS', [
                'url' => $url,
            ]);

        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FILE_TOO_LARGE', [
                'error' => 'The uploaded file exceeds the maximum allowed size.',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR', [
                'error' => $e->getMessage(),
            ]);
        }
    }

}
