<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','booth_id','price_snapshot'];

    protected $casts = ['price_snapshot' => 'integer'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function booth(): BelongsTo { return $this->belongsTo(Booth::class); }
}
