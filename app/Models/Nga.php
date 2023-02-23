<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nga extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'description',
        'email'
    ];
}
