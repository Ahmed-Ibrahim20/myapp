<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    protected Category $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * قائمة التصنيفات مع إمكانية البحث والتصفية
     */
    public function indexCategory($search = null, $perPage = 10)
    {
        return $this->model->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
        })->paginate($perPage);
    }

    /**
     * إنشاء تصنيف جديد مع رفع صورة إذا وجدت
     */
    public function storeCategory(array $requestData)
    {
        try {
            // تجهيز البيانات الأساسية
            $data = Arr::only($requestData, ['name', 'note']);
            $data['user_add_id'] = Auth::id();

            // التحقق من وجود صورة وتم رفعها كـ UploadedFile
            if (!empty($requestData['image']) && $requestData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['image'] = $this->storeImage($requestData['image']);
            }

            // إنشاء التصنيف
            $category = $this->model->create($data);

            return [
                'status'  => true,
                'message' => ' تم إنشاء التصنيف بنجاح',
                'data'    => $category,
            ];
        } catch (\Exception $e) {
            Log::error('❌ Category creation failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'حدث خطأ أثناء إنشاء التصنيف',
            ];
        }
    }

    /**
     * استرجاع بيانات تصنيف للتعديل
     */
    public function editCategory($categoryId)
    {
        return $this->model->find($categoryId);
    }

    /**
     * تحديث بيانات تصنيف مع معالجة الصورة
     */
    public function updateCategory(array $requestData, $categoryId)
    {
        try {
            $category = $this->model->find($categoryId);
            if (!$category) {
                return [
                    'status' => false,
                    'message' => 'التصنيف غير موجود'
                ];
            }
            $data = Arr::only($requestData, [
                'name',
                'note',
            ]);
            // معالجة الصورة إذا وجدت
            if (isset($requestData['image']) && $requestData['image']) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                $data['image'] = $this->storeImage($requestData['image']);
            }
            $category->update($data);
            return [
                'status' => true,
                'message' => 'تم تحديث بيانات التصنيف بنجاح',
                'data' => $category
            ];
        } catch (\Exception $e) {
            Log::error('Category update failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء التحديث'
            ];
        }
    }

    /**
     * حذف تصنيف
     */
    public function destroyCategory($categoryId)
    {
        try {
            $category = $this->model->find($categoryId);
            if (!$category) {
                return [
                    'status' => false,
                    'message' => 'التصنيف غير موجود'
                ];
            }
            // حذف الصورة إذا كانت موجودة
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            return [
                'status' => true,
                'message' => 'تم حذف التصنيف بنجاح'
            ];
        } catch (\Exception $e) {
            Log::error('Category deletion failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف التصنيف'
            ];
        }
    }

    /**
     * رفع صورة التصنيف وتخزينها
     */
    protected function storeImage(\Illuminate\Http\UploadedFile $image): string
    {
        $folder = 'assets/categories';
        $publicPath = public_path($folder);

        // تأكد أن المجلد موجود، ولو مش موجود أنشئه
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true); // التصاريح: قراءة وكتابة للمجلد
        }

        // استخراج الاسم الأصلي والامتداد
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();

        // توليد اسم فريد: name_20250520_210452.jpg
        $fileName = $originalName . '_' . now()->format('Ymd_His') . '.' . $extension;

        // المسار النهائي للملف
        $image->move($publicPath, $fileName);

        // اللي هيتخزن في قاعدة البيانات (نسبياً من public)
        return url($folder . '/' . $fileName);
    }
}
