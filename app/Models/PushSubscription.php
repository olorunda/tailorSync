<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PushSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    /**
     * Get the parent subscribable model.
     */
    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Convert the subscription to the format expected by the WebPush library.
     *
     * @return array
     */
    public function toWebPushFormat(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->public_key,
                'auth' => $this->auth_token,
            ],
            'contentEncoding' => $this->content_encoding ?? 'aes128gcm',
        ];
    }
}
