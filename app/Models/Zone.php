<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['event_id','name','color'];

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function booths(): HasMany { return $this->hasMany(Booth::class); }
}
