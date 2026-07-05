<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'visitor_uuid',
        'session_id',
        'user_id',
        'item_id',
        'event_name',
        'event_category',
        'page_type',
        'path',
        'value',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'value' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class)->withDefault();
    }
}
