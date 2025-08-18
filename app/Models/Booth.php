<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booth extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'AVAILABLE';
    public const STATUS_ON_HOLD   = 'ON_HOLD';
    public const STATUS_BOOKED    = 'BOOKED';
    public const STATUS_DISABLED  = 'DISABLED';

    protected $fillable = [
        'event_id','zone_id','code','base_price','status','expires_at'
    ];

    protected $casts = [
        'base_price' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function zone(): BelongsTo { return $this->belongsTo(Zone::class); }

    /* Scopes */
    public function scopeByEvent($q, $eventId){ return $q->where('event_id',$eventId); }
    public function scopeAvailable($q){ return $q->where('status', self::STATUS_AVAILABLE); }
    public function scopeHolded($q){ return $q->where('status', self::STATUS_ON_HOLD); }
    public function scopeBookeds($q){ return $q->where('status', self::STATUS_BOOKED); }

    /* Helpers */
    public function isAvailable(): bool { return $this->status === self::STATUS_AVAILABLE; }
    public function isOnHold(): bool { return $this->status === self::STATUS_ON_HOLD; }
    public function isBooked(): bool { return $this->status === self::STATUS_BOOKED; }
}
