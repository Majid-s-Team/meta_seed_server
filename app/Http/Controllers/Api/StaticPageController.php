<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaticPage;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;

class StaticPageController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all static pages
     */
    public function index()
    {
        $pages = StaticPage::all();
        return $this->successResponse('SUCCESS', $pages);
    }

    /**
     * Get specific static page by type
     */
    public function show($type)
    {
        $page = StaticPage::where('type', $type)->first();

        if (!$page) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'PAGE_NOT_FOUND');
        }

        return $this->successResponse('SUCCESS', $page);
    }

    /**
     * Create a new static page
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|unique:static_pages,type',
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $page = StaticPage::create($data);

        return $this->createdResponse('PAGE_CREATED', $page);
    }

    /**
     * Update a static page by type
     */
    public function update(Request $request, $type)
    {
        $page = StaticPage::where('type', $type)->first();

        if (!$page) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'PAGE_NOT_FOUND');
        }

        $data = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
        ]);

        $page->update($data);

        return $this->updatedResponse('PAGE_UPDATED', $page);
    }

    /**
     * Delete a static page by type
     */
    public function destroy($type)
    {
        $page = StaticPage::where('type', $type)->first();

        if (!$page) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'PAGE_NOT_FOUND');
        }

        $page->delete();

        return $this->successResponse('PAGE_DELETED');
    }
}
