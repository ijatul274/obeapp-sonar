<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LoginSlide extends Model
{
    protected $table = 'obe_carousel_login';

    protected $fillable = ['image_path', 'title', 'caption', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) return '';
        // External URL pass-through (jika seseorang menyimpan URL penuh)
        if (preg_match('#^https?://#i', $this->image_path)) {
            return $this->image_path;
        }
        return Storage::url($this->image_path);
    }
}
