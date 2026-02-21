<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'ticket_id', 'file_path', 'original_name', 'mime_type', 'file_size'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
