<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'state_id', 'name'
    ];

    protected $keyType = 'string';
    public $incrementing = false;



    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // public function districts()
    // {
    //     return $this->hasMany(District::class);
    // }

    public function country()
    {
        return $this->state->country();
    }
}
