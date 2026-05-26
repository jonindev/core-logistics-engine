<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = ['carrier_id', 'tracking_number', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
