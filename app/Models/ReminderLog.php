<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'remindable_id',
        'remindable_type',
        'reminder_type',
        'sent_at',
    ];

    public function remindable()
    {
        return $this->morphTo();
    }
}
