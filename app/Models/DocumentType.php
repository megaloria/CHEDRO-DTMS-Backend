<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'description',
        'days'
    ];

    protected $cast = [
        'days' => 'integer'
    ];
}
