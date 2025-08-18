<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',      // contoh: 'midtrans'
        'event',         // contoh: 'payment.notification'
        'raw_payload',   // jsonb
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'processed'   => 'boolean',
        'processed_at'=> 'datetime',
    ];

    /* Scopes */
    public function scopeUnprocessed(Builder $q): Builder
    {
        return $q->where('processed', false);
    }

    public function scopeProvider(Builder $q, string $provider): Builder
    {
        return $q->where('provider', $provider);
    }
}
