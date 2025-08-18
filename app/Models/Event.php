<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'location', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class, 'event_id'); // singular di sini
    }

    public function booths(): HasMany
    {
        return $this->hasMany(Booth::class, 'event_id'); // singular di sini
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /* Scopes */
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeUpcoming($q){ return $q->where('starts_at','>=',now()); }
}
