<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_name',
        'transformations',
        'output_image',
    ];

}
