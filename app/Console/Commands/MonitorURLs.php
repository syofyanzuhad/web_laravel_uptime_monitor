<?php

namespace App\Console\Commands;

use App\Jobs\RunCheck;
use App\Models\CustomerSite;
use Illuminate\Console\Command;

class MonitorURLs extends Command
{
    protected $signature = 'monitor:urls';

    protected $description = 'Monitor the given URLs';

    public function handle()
    {
        $customerSites = CustomerSite::where('is_active', 1)
            // ->orderByRaw("FIELD (priority_code, 'high', 'normal', 'low')")
            ->orderByRaw("CASE priority_code 
                    WHEN 'high' THEN 0
                    WHEN 'normal' THEN 1
                    WHEN 'low' THEN 2
                END")
            ->get(); // Add your desired URLs here

        foreach ($customerSites as $customerSite) {
            if (!$customerSite->needToCheck()) {
                continue;
            }

            dispatch(new RunCheck($customerSite));
        }

        $this->info('URLs monitored successfully.');
    }
}
