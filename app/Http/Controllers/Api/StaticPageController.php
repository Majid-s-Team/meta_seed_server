<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaticPage;

class StaticPageController extends Controller
{
    public function index()
    {
        return response()->json(StaticPage::all());
    }

    public function show($type)
    {
        $page = StaticPage::where('type', $type)->firstOrFail();
        return response()->json($page);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|unique:static_pages,type',
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $page = StaticPage::create($data);
        return response()->json(['message' => 'Page created', 'data' => $page]);
    }

    public function update(Request $request, $type)
    {
        $page = StaticPage::where('type', $type)->firstOrFail();

        $data = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
        ]);

        $page->update($data);
        return response()->json(['message' => 'Page updated', 'data' => $page]);
    }

    public function destroy($type)
    {
        $page = StaticPage::where('type', $type)->firstOrFail();
        $page->delete();

        return response()->json(['message' => 'Page deleted']);
    }
}