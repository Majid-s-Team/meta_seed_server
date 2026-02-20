<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\StaticPageVersion;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    protected array $types = ['privacy', 'terms', 'about', 'faq'];

    public function index()
    {
        $pages = StaticPage::whereIn('type', $this->types)->get()->keyBy('type');
        $types = $this->types;
        return view('admin.cms.index', compact('pages', 'types'));
    }

    public function edit(string $type)
    {
        if (!in_array($type, $this->types, true)) {
            abort(404);
        }
        $page = StaticPage::firstOrCreate(
            ['type' => $type],
            ['title' => ucfirst($type), 'content' => '']
        );
        return view('admin.cms.edit', compact('page'));
    }

    public function preview(string $type)
    {
        if (!in_array($type, $this->types, true)) {
            abort(404);
        }
        $page = StaticPage::where('type', $type)->first();
        if (!$page) {
            $page = (object)['title' => ucfirst($type), 'content' => ''];
        }
        return view('admin.cms.preview', compact('page'));
    }

    public function update(Request $request, string $type)
    {
        if (!in_array($type, $this->types, true)) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        // Basic sanitization: remove script/iframe/object to prevent XSS
        $validated['content'] = preg_replace('/<(script|iframe|object|embed)\b[^>]*>.*?<\/\1>/is', '', $validated['content']);
        $page = StaticPage::updateOrCreate(
            ['type' => $type],
            $validated
        );
        StaticPageVersion::create([
            'type' => $type,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'created_by' => auth()->id(),
        ]);
        return redirect()->route('admin.cms.index')->with('success', 'Page updated.');
    }
}
