<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['group','key','value'];
    protected $casts = ['value' => 'array'];

    // helper static
    public static function get(string $group, string $key, $default = null)
    {
        static $cache = [];
        $idx = "$group.$key";
        if (!array_key_exists($idx, $cache)) {
            $row = self::query()->where(compact('group','key'))->first();
            $cache[$idx] = $row? $row->value : $default;
        }
        return $cache[$idx] ?? $default;
    }

    public static function set(string $group, string $key, $val): void
    {
        self::updateOrCreate(['group'=>$group,'key'=>$key], ['value'=>$val]);
    }
}
