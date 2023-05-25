<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hei extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'uii',
        'name',
        'street_barangay',
        'city_municipality',
        'province',
        'head_of_institution',
        'email'
    ];

    protected $appends = [
        'title'
    ];

    public function getTitleAttribute() {
        return $this->name;
    }

    public function sender(){
        return $this->morphOne('App\Models\Sender','receivable')->cascadeOnDelete();
    }
}
