<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsPageView extends Model
{
    protected $fillable = [
        'visitor_uuid',
        'session_id',
        'user_id',
        'item_id',
        'route_name',
        'page_type',
        'page_title',
        'path',
        'full_url',
        'query_string',
        'referrer',
        'referrer_host',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'device_type',
        'browser',
        'platform',
        'ip_hash',
        'user_agent',
        'is_bot',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class)->withDefault();
    }
}
