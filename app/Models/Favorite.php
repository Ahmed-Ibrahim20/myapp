<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    /**
     * الحقول التي يمكن تعبئتها جماعياً
     * 
     * @var array
     */
    protected $fillable = [
        'user_id',
        'product_id'
    ];

    /**
     * التواريخ التي يجب معاملتها كمثيلات Carbon
     * 
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * العلاقة مع جدول المستخدمين
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * العلاقة مع جدول المنتجات
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    /**
     * نطاق البحث حسب المستخدم
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * نطاق البحث حسب المنتج
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * إضافة منتج إلى المفضلة
     * 
     * @param int $userId
     * @param int $productId
     * @return \App\Models\Favorite
     */
    public static function addToFavorites($userId, $productId)
    {
        return self::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
    }

    /**
     * إزالة منتج من المفضلة
     * 
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public static function removeFromFavorites($userId, $productId)
    {
        return self::where('user_id', $userId)
                  ->where('product_id', $productId)
                  ->delete();
    }

    /**
     * التحقق من وجود منتج في مفضلة المستخدم
     * 
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public static function isFavorite($userId, $productId)
    {
        return self::where('user_id', $userId)
                  ->where('product_id', $productId)
                  ->exists();
    }
}