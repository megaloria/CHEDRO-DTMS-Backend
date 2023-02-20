<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HEIS extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'uii',
        'name',
        'head_of_institution'
    ];
}
