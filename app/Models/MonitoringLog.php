<?php

namespace App\Models;

use App\Models\CustomerSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class MonitoringLog extends Model
{
    use HasFactory, Prunable;

    protected $fillable = ['customer_site_id', 'url', 'response_time', 'status_code'];

    public function customerSite()
    {
        return $this->belongsTo(CustomerSite::class)->withDefault(['name' => 'n/a']);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonth());
    }
}
