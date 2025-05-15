<?php

namespace App\Console\Commands;

use App\Services\MetricsService;
use Illuminate\Support\Facades\Log;

class CollectCpuMetricsCommand extends AbstractSendMetricsCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect-cpu
                            {node_id : The ID of the status node to collect metrics for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect CPU usage metrics and send them to the status node';

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\MetricsService  $metricsService
     * @return void
     */
    public function __construct(MetricsService $metricsService)
    {
        parent::__construct($metricsService);
    }

    /**
     * Collect CPU metrics from the system.
     *
     * @return array
     */
    protected function collectMetrics(): array
    {
        $metrics = [];
        
        // Collect CPU usage metrics (cross-platform approach)
        try {
            // Get CPU load metrics
            $cpuLoad = $this->getCpuLoad();
            $metrics[] = [
                'name' => 'cpu_load',
                'group' => 'system',
                'value' => $cpuLoad,
            ];
            
            // Get CPU usage per core if available
            $coreUsages = $this->getCpuCoreUsages();
            foreach ($coreUsages as $core => $usage) {
                $metrics[] = [
                    'name' => "cpu_core_{$core}_usage",
                    'group' => 'system',
                    'value' => $usage,
                ];
            }
            
            // Get load averages (Unix-like systems)
            $loadAverages = $this->getLoadAverages();
            if (!empty($loadAverages)) {
                $metrics[] = [
                    'name' => 'load_avg_1min',
                    'group' => 'system',
                    'value' => $loadAverages[0],
                ];
                
                if (isset($loadAverages[1])) {
                    $metrics[] = [
                        'name' => 'load_avg_5min',
                        'group' => 'system',
                        'value' => $loadAverages[1],
                    ];
                }
                
                if (isset($loadAverages[2])) {
                    $metrics[] = [
                        'name' => 'load_avg_15min',
                        'group' => 'system',
                        'value' => $loadAverages[2],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error collecting CPU metrics: ' . $e->getMessage());
            $this->error('Error collecting CPU metrics: ' . $e->getMessage());
        }
        
        return $metrics;
    }
    
    /**
     * Get the overall CPU load as a percentage.
     *
     * @return float
     */
    protected function getCpuLoad(): float
    {
        $cpuLoad = 0;
        
        if (function_exists('sys_getloadavg') && is_array(sys_getloadavg())) {
            // On Unix-like systems, we can use sys_getloadavg() and normalize
            $loadAvg = sys_getloadavg();
            $cpuCount = $this->getCpuCount();
            
            // Normalize to percentage (1.0 on 1 core = 100%)
            $cpuLoad = min(100, ($loadAvg[0] / $cpuCount) * 100);
        } else {
            // Fallback to a simulated value for demo environments
            // In a real-world scenario, you would use platform-specific methods
            // e.g., WMI on Windows, or parse /proc/stat on Linux
            $cpuLoad = mt_rand(5, 95);
            
            $this->warn('Using simulated CPU load for demonstration');
        }
        
        return round($cpuLoad, 2);
    }
    
    /**
     * Get CPU usage for each core.
     *
     * @return array
     */
    protected function getCpuCoreUsages(): array
    {
        $coreUsages = [];
        $cpuCount = $this->getCpuCount();
        
        // In a real application, you would use platform-specific methods to get per-core usage
        // For demo purposes, we'll generate random values
        for ($i = 0; $i < $cpuCount; $i++) {
            $coreUsages[$i] = round(mt_rand(5, 95), 2);
        }
        
        if (count($coreUsages) > 0) {
            $this->warn('Using simulated CPU core usages for demonstration');
        }
        
        return $coreUsages;
    }
    
    /**
     * Get the number of CPU cores.
     *
     * @return int
     */
    protected function getCpuCount(): int
    {
        $count = 1; // Default to 1 core
        
        if (is_file('/proc/cpuinfo')) {
            // Linux
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            $count = substr_count($cpuinfo, 'processor');
        } elseif (PHP_OS === 'Darwin') {
            // macOS
            $count = (int) shell_exec('sysctl -n hw.ncpu');
        } elseif (PHP_OS === 'WINNT') {
            // Windows
            $count = (int) getenv('NUMBER_OF_PROCESSORS');
        }
        
        return max(1, $count); // Ensure at least 1 core
    }
    
    /**
     * Get system load averages (1, 5, and 15 minutes).
     *
     * @return array
     */
    protected function getLoadAverages(): array
    {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        
        return [];
    }
}