<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * عرض قائمة التصنيفات مع البحث والتصفية
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $categories = $this->categoryService->indexCategory($search, $perPage);
        return response()->json($categories);
    }

    /**
     * إنشاء تصنيف جديد
     */
    public function store(CategoryRequest $request)
    {
        $result = $this->categoryService->storeCategory($request->validated());
        if ($result['status']) {
            return response()->json($result, Response::HTTP_CREATED);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * عرض تفاصيل تصنيف
     */
    public function show($id)
    {
        $category = $this->categoryService->editCategory($id);
        if ($category) {
            return response()->json($category);
        }
        return response()->json(['status' => false, 'message' => 'التصنيف غير موجود'], Response::HTTP_NOT_FOUND);
    }

    /**
     * تحديث بيانات تصنيف
     */
    public function update(CategoryRequest $request, $id)
    {
        $result = $this->categoryService->updateCategory($request->validated(), $id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * حذف تصنيف
     */
    public function destroy($id)
    {
        $result = $this->categoryService->destroyCategory($id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }
}