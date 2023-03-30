<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_id'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'to_id');
    }
}
