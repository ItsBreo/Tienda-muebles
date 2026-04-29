<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $table = 'furniture_images';

    protected $fillable = [
        'furniture_id',
        'image_path',
        'is_primary',
        'display_order',
        'alt_text',
    ];

    public function furniture()
    {
        return $this->belongsTo(Furniture::class);
    }
}
