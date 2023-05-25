<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
     use HasFactory;

      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'position_designation'
    ];

    protected $appends = [
        'name'
    ];

    public function getNameAttribute() {
        return ($this->prefix ? $this->prefix . ' ': '').$this->first_name . ' ' . $this->last_name. ($this->suffix ? $this->suffix : '');
    }
    
}




