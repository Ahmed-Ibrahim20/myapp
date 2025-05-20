<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    // تعريف أنواع المنتجات كثوابت
    const TYPE_DEFAULT = 0;
    const TYPE_KG = 1;
    const TYPE_UNIT = 2;

    /**
     * الحقول التي يمكن تعبئتها جماعياً
     * 
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'type',
        'image',
        'user_add_id',
        'category_id'
    ];

    /**
     * تحويل أنواع الحقول
     * 
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * القيم الافتراضية للحقول
     * 
     * @var array
     */
    protected $attributes = [
        'price' => 0.00,
        'quantity' => 0,
        'type' => self::TYPE_DEFAULT,
    ];

    /**
     * العلاقة مع جدول المستخدمين (من أضاف المنتج)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_add_id')->withDefault();
    }

    /**
     * العلاقة مع جدول التصنيفات
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    /**
     * Accessor لمسار الصورة الكامل
     * 
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        
        return $this->image ? asset('storage/products/' . $this->image) : asset('images/default-product.png');
    }

    /**
     * Accessor لاسم نوع المنتج
     * 
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_KG => 'كيلوجرام',
            self::TYPE_UNIT => 'قطعة',
            default => 'غير محدد',
        };
    }

    /**
     * Mutator لتنظيف اسم المنتج قبل الحفظ
     * 
     * @param string $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = trim(ucwords($value));
    }

    /**
     * Mutator لضبط السعر قبل الحفظ
     * 
     * @param mixed $value
     * @return void
     */
    public function setPriceAttribute($value): void
    {
        $this->attributes['price'] = number_format((float)$value, 2, '.', '');
    }

    /**
     * نطاق البحث عن المنتجات المتاحة (كمية > 0)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * نطاق البحث حسب التصنيف
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}