<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'document_id',
        'file_name',
        'file_title'
    ];

    protected $casts = [
        'document_id' => 'integer'
    ];
}
