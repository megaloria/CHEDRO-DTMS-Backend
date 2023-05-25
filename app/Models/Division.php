<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'description'
    ];

    public function role() {
        return $this->hasOne('App\Models\Role');
    }
}
