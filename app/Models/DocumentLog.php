<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_id',
        'from_id',
        'acknowledge_id',
        'action_id',
        'comment',
        'approved_id',
        'rejected_id',
        'released_at'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'to_id');
    }

    public function acknowledge_user() {
        return $this->belongsTo('App\Models\User', 'acknowledge_id');
    }
    
    public function action_user() {
        return $this->belongsTo('App\Models\User', 'action_id');
    }

    public function approved_user() {
        return $this->belongsTo('App\Models\User', 'approved_id');
    }

    public function rejected_user() {
        return $this->belongsTo('App\Models\User', 'rejected_id');
    }

    public function from_user() {
        return $this->belongsTo('App\Models\User', 'from_id');
    }

    public function assigned_user() {
        return $this->belongsTo('App\Models\User', 'assigned_id');
    }
}
