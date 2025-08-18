<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_email',
        'subject',
        'template', // optional: nama blade template/email type
        'status',   // SENT | FAILED
        'error',    // detail error kalau gagal
    ];

    protected $casts = [
        // none khusus; tambah kalau perlu
    ];

    /* Scopes */
    public function scopeSent(Builder $q): Builder
    {
        return $q->where('status', 'SENT');
    }

    public function scopeFailed(Builder $q): Builder
    {
        return $q->where('status', 'FAILED');
    }
}
