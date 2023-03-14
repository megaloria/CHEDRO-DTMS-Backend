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
        'date_received'
    ];

    protected $casts = [
        'document_type_id' => 'integer',
        'user_id' => 'integer',
        'sender_id' => 'integer'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

   public function documentType() {
        return $this->belongsTo('App\Models\DocumentType');
    }

    public function attachments() {
         return $this->hasMany('App\Models\Attachment');
    }


    public function sender(){
        return $this->belongsTo('App\Models\Sender');
    }
}
