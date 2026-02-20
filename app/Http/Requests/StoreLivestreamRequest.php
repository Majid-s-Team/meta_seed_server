<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivestreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'agora_channel' => 'required|string|max:64',
            'broadcast_type' => 'nullable|in:agora_rtc,rtmp',
            'rtmp_url' => 'nullable|string|max:500',
            'rtmp_stream_key' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
        ];
    }
}
