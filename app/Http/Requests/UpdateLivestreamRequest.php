<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLivestreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'sometimes|date',
            'agora_channel' => 'sometimes|string|max:64',
            'broadcast_type' => 'nullable|in:agora_rtc,rtmp',
            'rtmp_url' => 'nullable|string|max:500',
            'rtmp_stream_key' => 'nullable|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'max_participants' => 'sometimes|integer|min:1',
        ];
    }
}
