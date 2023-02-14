<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'id',
        'document_type_id',
        'user_id',
        'tracking_no',
        'recieved_from',
        'description',
        'date_received'
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
    
}
