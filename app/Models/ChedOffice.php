<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChedOffice extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'description',
        'email'
    ];

    protected $appends = [
        'title'
    ];

    public function getTitleAttribute() {
        return $this->description;
    }

    public function sender(){
        return $this->morphOne('App\Models\Sender','receivable')->cascadeOnDelete();
    }
}
