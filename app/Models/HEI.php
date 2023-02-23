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
        'address',
        'head_of_institution'
    ];
}
