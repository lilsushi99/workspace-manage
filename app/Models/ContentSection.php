<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'description',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(ContentItem::class, 'section_id');
    }
}
