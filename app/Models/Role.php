<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'description',
        'division_id',
        'level'
    ];

        public function divisions() {
         return $this->belongsTo('App\Models\Division');
    }
}
