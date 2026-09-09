<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    protected $fillable = ['gateway', 'enabled', 'mode', 'default_currency'];

    protected $casts = ['enabled' => 'boolean'];
}
