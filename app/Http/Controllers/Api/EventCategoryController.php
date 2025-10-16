<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all event categories
     */
    public function index()
    {
        try {
            $categories = EventCategory::all();

            return $this->successResponse('SUCCESS', $categories);
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::INTERNAL_SERVER_ERROR,
                'SERVER_ERROR',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Create a new event category
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category = EventCategory::create($validated);

            return $this->apiResponse(
                ResponseCode::CREATED,
                'SUCCESS',
                $category
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                ResponseCode::VALIDATION_ERROR,
                'FAILED',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::INTERNAL_SERVER_ERROR,
                'SERVER_ERROR',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update an existing event category
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category = EventCategory::find($id);

            if (!$category) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            $category->update(['name' => $validated['name']]);

            return $this->successResponse('SUCCESS', $category);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                ResponseCode::VALIDATION_ERROR,
                'FAILED',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::INTERNAL_SERVER_ERROR,
                'SERVER_ERROR',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete a category
     */
    public function destroy($id)
    {
        try {
            $category = EventCategory::find($id);

            if (!$category) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            $category->delete();

            return $this->successResponse('SUCCESS', ['deleted_id' => $id]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::INTERNAL_SERVER_ERROR,
                'SERVER_ERROR',
                ['error' => $e->getMessage()]
            );
        }
    }
}
