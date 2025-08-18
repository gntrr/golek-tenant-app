<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;

class Order extends Model
{
    use HasFactory;

    public const METHOD_MIDTRANS    = 'MIDTRANS';
    public const METHOD_BANK        = 'BANK_TRANSFER';

    public const STATUS_PENDING     = 'PENDING';
    public const STATUS_AWAITING    = 'AWAITING_PAYMENT';
    public const STATUS_PAID        = 'PAID';
    public const STATUS_EXPIRED     = 'EXPIRED';
    public const STATUS_CANCELLED   = 'CANCELLED';

    protected $fillable = [
        'event_id','customer_name','email','phone','company_name',
        'invoice_number','total_amount','payment_method','status','expires_at',
    ];

    protected $casts = [
        'total_amount'  => 'integer',
        'expires_at'    => 'datetime',
    ];

    /* Relations */
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    /* Scopes */
    public function scopePaid($q){ return $q->where('status', self::STATUS_PAID); }
    public function scopePending($q){ return $q->where('status', self::STATUS_PENDING); }

    /* Accessors */
    public function totalFormatted(): Attribute
    {
        return Attribute::get(fn() => number_format($this->total_amount, 0, ',', '.'));
    }
}
