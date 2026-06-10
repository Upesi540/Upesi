<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ratings extends Model
{
    use HasFactory , HasUuids , SoftDeletes;
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'property_id',
        'user_id',
        'rating',
    ];

    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function properties()
    {
        return $this->belongsTo(Property::class,'property_id');
    }
}
