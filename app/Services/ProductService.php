<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    protected Product $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * قائمة المنتجات مع إمكانية البحث والتصفية
     */
    public function indexProduct($search = null, $perPage = 10)
    {
        return $this->model->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })->paginate($perPage);
    }

    /**
     * إنشاء منتج جديد مع رفع صورة إذا وجدت
     */
    public function storeProduct(array $requestData)
    {
        try {
            $data = Arr::only($requestData, ['name', 'description', 'price', 'quantity', 'type', 'category_id']);
            $data['user_add_id'] = Auth::id();

            // التحقق من وجود صورة وتم رفعها كـ UploadedFile
            if (!empty($requestData['image']) && $requestData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['image'] = $this->storeImage($requestData['image']);
            } else {
                $data['image'] = $requestData['image'] ?? null;
            }

            $product = $this->model->create($data);

            return [
                'status'  => true,
                'message' => 'تم إنشاء المنتج بنجاح',
                'data'    => $product,
            ];
        } catch (\Exception $e) {
            Log::error('❌ Product creation failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'حدث خطأ أثناء إنشاء المنتج',
            ];
        }
    }

    /**
     * استرجاع بيانات منتج للتعديل
     */
    public function editProduct($productId)
    {
        return $this->model->find($productId);
    }
    public function getProductsByCategory($categoryId)
    {
        return $this->model->byCategory($categoryId)->get();
    }
    /**
     * تحديث بيانات منتج مع معالجة الصورة
     */
    public function updateProduct(array $requestData, $productId)
    {
        try {
            $product = $this->model->find($productId);
            if (!$product) {
                return [
                    'status' => false,
                    'message' => 'المنتج غير موجود'
                ];
            }
            $data = Arr::only($requestData, [
                'name',
                'description',
                'price',
                'quantity',
                'type',
                'category_id',
            ]);
            // معالجة الصورة إذا وجدت
            if (isset($requestData['image']) && $requestData['image']) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($product->image && file_exists(public_path(parse_url($product->image, PHP_URL_PATH)))) {
                    unlink(public_path(parse_url($product->image, PHP_URL_PATH)));
                }
                $data['image'] = $this->storeImage($requestData['image']);
            }
            $product->update($data);
            return [
                'status' => true,
                'message' => 'تم تحديث بيانات المنتج بنجاح',
                'data' => $product
            ];
        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء التحديث'
            ];
        }
    }

    /**
     * حذف منتج
     */
    public function destroyProduct($productId)
    {
        try {
            $product = $this->model->find($productId);
            if (!$product) {
                return [
                    'status' => false,
                    'message' => 'المنتج غير موجود'
                ];
            }
            // حذف الصورة إذا كانت موجودة
            if ($product->image && file_exists(public_path(parse_url($product->image, PHP_URL_PATH)))) {
                unlink(public_path(parse_url($product->image, PHP_URL_PATH)));
            }
            $product->delete();
            return [
                'status' => true,
                'message' => 'تم حذف المنتج بنجاح'
            ];
        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف المنتج'
            ];
        }
    }

    /**
     * رفع صورة المنتج وتخزينها
     */
    protected function storeImage(\Illuminate\Http\UploadedFile $image): string
    {
        $folder = 'assets/images';
        $publicPath = public_path($folder);

        // تأكد أن المجلد موجود، ولو مش موجود أنشئه
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }

        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileName = $originalName . '_' . now()->format('Ymd_His') . '.' . $extension;
        $image->move($publicPath, $fileName);
        return url($folder . '/' . $fileName);
    }
}