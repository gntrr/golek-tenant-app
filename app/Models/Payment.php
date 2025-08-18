<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const PROVIDER_MIDTRANS = 'MIDTRANS';
    public const PROVIDER_BANK     = 'BANK_TRANSFER';

    public const STATUS_INITIATED  = 'INITIATED';
    public const STATUS_PENDING    = 'PENDING';
    public const STATUS_SETTLEMENT = 'SETTLEMENT';
    public const STATUS_DENY       = 'DENY';
    public const STATUS_EXPIRE     = 'EXPIRE';
    public const STATUS_CANCEL     = 'CANCEL';

    protected $fillable = [
        'order_id','provider','amount','status',
        'midtrans_txn_id','va_number','bank','paid_at','raw_payload',
    ];

    protected $casts = [
        'amount'     => 'integer',
        'paid_at'    => 'datetime',
        'raw_payload'=> 'array', // jsonb
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
