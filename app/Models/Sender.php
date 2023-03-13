<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'receivable_table',
        'receivable_id',
        'name'
    ];

    public function receivable() 
    {
        return $this->morphTo();
    }

    
}
