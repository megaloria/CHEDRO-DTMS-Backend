<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAssignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_id'
    ];

}
