<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * عرض قائمة المنتجات مع البحث والتصفية
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $products = $this->productService->indexProduct($search, $perPage);
        return response()->json($products);
    }

    /**
     * إنشاء منتج جديد
     */
    public function store(ProductRequest $request)
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
        }
        $result = $this->productService->storeProduct($validated);
        if ($result['status']) {
            return response()->json($result, Response::HTTP_CREATED);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * عرض تفاصيل منتج
     */
    public function show($id)
    {
        $product = $this->productService->editProduct($id);
        if ($product) {
            return response()->json($product);
        }
        return response()->json(['status' => false, 'message' => 'المنتج غير موجود'], Response::HTTP_NOT_FOUND);
    }

    /**
     * تحديث بيانات منتج
     */
    public function update(ProductRequest $request, $id)
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
        }
        $result = $this->productService->updateProduct($validated, $id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * حذف منتج
     */
    public function destroy($id)
    {
        $result = $this->productService->destroyProduct($id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * جلب كل المنتجات حسب التصنيف
     */
    public function productsByCategory($categoryId)
    {
        $products = $this->productService->getProductsByCategory($categoryId);
        return response()->json([
            'status' => true,
            'message' => 'قائمة المنتجات حسب التصنيف',
            'data' => $products
        ]);
    }
}