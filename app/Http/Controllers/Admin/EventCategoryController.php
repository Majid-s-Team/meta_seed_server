<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function index()
    {
        $categories = EventCategory::withCount('events')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        EventCategory::create($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(EventCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, EventCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category->update($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(EventCategory $category)
    {
        if ($category->events()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category that has events. Assign events to another category first.');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        } else {
            $ids = array_filter((array) ($ids ?? []));
        }
        if (empty($ids)) {
            return redirect()->route('admin.categories.index')->with('error', 'No categories selected.');
        }
        $categories = EventCategory::whereIn('id', $ids)->get();
        $deleted = 0;
        foreach ($categories as $cat) {
            if (!$cat->events()->exists()) {
                $cat->delete();
                $deleted++;
            }
        }
        if ($deleted === 0 && $categories->isNotEmpty()) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete categories that have events.');
        }
        return redirect()->route('admin.categories.index')->with('success', $deleted ? "{$deleted} categor" . ($deleted === 1 ? 'y' : 'ies') . " deleted." : 'No categories deleted.');
    }
}
