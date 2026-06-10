<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Country extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'iso3', 'iso2', 'phone_code', 'capital', 'currency', 'native', 'emoji', 'emojiU'
    ];

    protected $keyType = 'string';
    public $incrementing = false;
    

    public function states()
    {
        return $this->hasMany(State::class);
    }
}
