<?php

namespace App\Traits;

use App\Constants\ResponseCode;
use Illuminate\Support\Facades\Storage;
trait ApiResponseTrait
{
    /**
     * Base API Response format
     */
    protected function apiResponse($statusCode, $messageKey, $data = null)
    {
        $message = config('response.' . $messageKey) ?? $messageKey;

        return response()->json([
            'status_code' => $statusCode,
            'message'     => $message,
            'data'        => $data,
        ], $statusCode);
    }

    /**
     * Success Response
     */
    protected function successResponse($messageKey = 'SUCCESS', $data = null)
    {
        return $this->apiResponse(ResponseCode::SUCCESS, $messageKey, $data);
    }

    /**
     * Created Response
     */
    protected function createdResponse($messageKey = 'SUCCESS', $data = null)
    {
        return $this->apiResponse(ResponseCode::CREATED, $messageKey, $data);
    }

    /**
     * Updated Response
     */
    protected function updatedResponse($messageKey = 'SUCCESS', $data = null)
    {
        return $this->apiResponse(ResponseCode::UPDATED, $messageKey, $data);
    }

    /**
     * Error Response
     */
    protected function errorResponse($statusCode, $messageKey = 'FAILED', $data = null)
    {
        return $this->apiResponse($statusCode, $messageKey, $data);
    }
}
