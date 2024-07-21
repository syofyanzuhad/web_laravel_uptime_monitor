<?php

namespace App\Livewire;

use App\Models\CustomerSite;
use App\Models\MonitoringLog;
use Livewire\Component;

class UptimeBadge extends Component
{
    public $uptimePoll = 0;
    public $uptimePollState = null;
    public $customerSite;
    public $monitoringLogs;

    public function mount()
    {
        $customerSite = $this->customerSite;
        if ($this->uptimePoll == 1) {
            $checkPeriodeInSeconds = $customerSite->check_interval * 60;
            $this->uptimePollState = 'wire:poll.'.($checkPeriodeInSeconds).'s';
        }
    }

    public function render()
    {
        return view('livewire.uptime_badge');
    }

    private function getCustomerSiteMonitoringLogs(CustomerSite $customerSite)
    {
        $monitoringLogs = $customerSite->monitoringLogs()
            ->latest('id')
            ->take(15)
            ->get(['response_time', 'status_code', 'created_at'])
            ->keyBy(function($item) {
                return $item->created_at->format('Y-m-d H:i:00');
            });
    
        $last15Minutes = collect(range(0, 14))->map(function ($minuteAgo) use ($monitoringLogs, $customerSite) {
            $minute = now()->subMinutes($minuteAgo)->format('Y-m-d H:i:00');
            if ($monitoringLogs->has($minute)) {
                $log = $monitoringLogs->get($minute);
                $log->uptime_badge_bg_color = $this->getUptimeBadgeBgColor($customerSite, $log);
                $log->uptime_badge_title = $this->getUptimeBadgeTitle($log);
            } else {
                $log = (object)[
                    'created_at' => $minute,
                    'response_time' => null,
                    'status_code' => null,
                    'uptime_badge_bg_color' => 'secondary',
                    'uptime_badge_title' => $minute . ' (no data)',
                ];
            }
            return $log;
        });
    
        return $last15Minutes;
    }


    private function getUptimeBadgeBgColor(CustomerSite $customerSite, MonitoringLog $monitoringLog): string
    {
        if ($monitoringLog->status_code >= 500) {
            return 'danger';
        }
        if ($monitoringLog->status_code >= 400) {
            return 'danger';
        }
        if ($monitoringLog->response_time >= $customerSite->down_threshold) {
            return 'danger';
        }
        if ($monitoringLog->status_code >= 300) {
            return 'warning';
        }
        if ($monitoringLog->response_time >= $customerSite->warning_threshold) {
            return 'warning';
        }

        return 'success';
    }

    private function getUptimeBadgeTitle(MonitoringLog $monitoringLog): string
    {
        return $monitoringLog->created_at.' (time:'.number_format($monitoringLog->response_time).'ms code:'.$monitoringLog->status_code.')';
    }
}
