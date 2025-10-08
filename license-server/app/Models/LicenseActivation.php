<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    protected $fillable = ['license_id','fingerprint','ip','user_agent','last_seen'];
}
