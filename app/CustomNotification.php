<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomNotification extends Model
{
    protected $fillable = [
        'user_id',
        'notify',
        'enquirer_name',
        'contact_number',
        'email_address',
        'notification_type',
        'title',
        'message',
    ];

    protected $casts = [
        'notification_type' => 'array',
    ];
}
