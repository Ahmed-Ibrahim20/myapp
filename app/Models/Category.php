<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property string|null $note
 * @property string|null $image
 * @property int|null $user_add_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User|null $addedBy
 */
class Category extends Model
{
    /**
     * جدول قاعدة البيانات المرتبط بالموديل.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * الخصائص التي يمكن تعبئتها جماعيًا.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'note',
        'image',
        'user_add_id',
    ];

    /**
     * الـ casting للحقول (مثلاً للتواريخ).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * علاقة التصنيف بالمستخدم الذي أضافه.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_add_id');
    }
}
