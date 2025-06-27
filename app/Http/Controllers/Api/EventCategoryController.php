<?php

namespace App\Http\Controllers\Api;
use App\Models\{Event, EventCategory, EventBooking, Wallet, Transaction};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function index()
    {
        return response()->json(EventCategory::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        return response()->json(EventCategory::create($request->only('name')));
    }

    public function update(Request $request, $id)
    {
        $category = EventCategory::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $category->update(['name' => $request->name]);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        EventCategory::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}