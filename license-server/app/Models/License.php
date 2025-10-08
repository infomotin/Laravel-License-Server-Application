<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = ['key','status','bound_identifier','meta','expires_at'];
    protected $casts = ['meta' => 'array', 'expires_at' => 'datetime'];
}
