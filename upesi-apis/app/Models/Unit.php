<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name', 'symbol', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function crops()
    {
        return $this->hasMany(Crop::class, 'default_unit_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'default_unit_id');
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'unit_id');
    }
}
