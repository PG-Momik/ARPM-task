<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];


    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper($value),
        );
    }
}