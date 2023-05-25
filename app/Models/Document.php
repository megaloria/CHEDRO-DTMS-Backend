<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'document_type_id',
        'user_id',
        'tracking_no',
        'sender_id',
        'category_id',
        'description',
        'date_received',
        'series_no'
    ];

    protected $casts = [
        'document_type_id' => 'integer',
        'user_id' => 'integer',
        'sender_id' => 'integer',
        'series_no' => 'integer'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

   public function documentType() {
        return $this->belongsTo('App\Models\DocumentType');
    }

    public function attachments() {
         return $this->hasOne('App\Models\Attachment');
    }

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }

    public function sender(){
        return $this->belongsTo('App\Models\Sender', 'sender_id');
    }

    public function logs(){
        return $this->hasMany('App\Models\DocumentLog');
    }

     public function assign(){
        return $this->hasMany('App\Models\DocumentAssignation');
    }
}
