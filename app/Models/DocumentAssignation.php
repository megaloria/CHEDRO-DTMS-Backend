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

    public function assigned_user() {
        return $this->belongsTo('App\Models\User', 'assigned_id');
    }

}
