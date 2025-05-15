<?php

namespace App\Console\Commands;

use App\Services\MetricsService;
use Illuminate\Support\Facades\Log;

class CollectMemoryMetricsCommand extends AbstractSendMetricsCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect-memory
                            {node_id : The ID of the status node to collect metrics for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect memory usage metrics and send them to the status node';

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
     * Collect memory metrics from the system.
     *
     * @return array
     */
    protected function collectMetrics(): array
    {
        $metrics = [];
        
        try {
            // Get memory usage metrics
            $memoryInfo = $this->getMemoryInfo();
            
            if (isset($memoryInfo['usage_percent'])) {
                $metrics[] = [
                    'name' => 'memory_usage',
                    'group' => 'system',
                    'value' => $memoryInfo['usage_percent'],
                ];
            }
            
            if (isset($memoryInfo['total'])) {
                $metrics[] = [
                    'name' => 'memory_total',
                    'group' => 'system',
                    'value' => $memoryInfo['total'],
                ];
            }
            
            if (isset($memoryInfo['used'])) {
                $metrics[] = [
                    'name' => 'memory_used',
                    'group' => 'system',
                    'value' => $memoryInfo['used'],
                ];
            }
            
            if (isset($memoryInfo['free'])) {
                $metrics[] = [
                    'name' => 'memory_free',
                    'group' => 'system',
                    'value' => $memoryInfo['free'],
                ];
            }
            
            if (isset($memoryInfo['swap_usage_percent'])) {
                $metrics[] = [
                    'name' => 'swap_usage',
                    'group' => 'system',
                    'value' => $memoryInfo['swap_usage_percent'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error collecting memory metrics: ' . $e->getMessage());
            $this->error('Error collecting memory metrics: ' . $e->getMessage());
        }
        
        return $metrics;
    }
    
    /**
     * Get memory usage information from the system.
     *
     * @return array
     */
    protected function getMemoryInfo(): array
    {
        $memoryInfo = [];
        
        if (PHP_OS === 'Linux') {
            // Linux - read from /proc/meminfo
            $memoryInfo = $this->getLinuxMemoryInfo();
        } elseif (PHP_OS === 'Darwin') {
            // macOS - use vm_stat command
            $memoryInfo = $this->getMacMemoryInfo();
        } elseif (PHP_OS === 'WINNT') {
            // Windows - use wmic command
            $memoryInfo = $this->getWindowsMemoryInfo();
        } else {
            // Fallback to simulated values for demo
            $memoryInfo = $this->getSimulatedMemoryInfo();
        }
        
        return $memoryInfo;
    }
    
    /**
     * Get memory info on Linux.
     *
     * @return array
     */
    protected function getLinuxMemoryInfo(): array
    {
        $memoryInfo = [];
        
        if (is_file('/proc/meminfo')) {
            $meminfoContent = file_get_contents('/proc/meminfo');
            $lines = explode("\n", $meminfoContent);
            $values = [];
            
            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s+(\d+)/', $line, $matches)) {
                    $values[$matches[1]] = intval($matches[2]) * 1024; // Convert kB to bytes
                }
            }
            
            if (isset($values['MemTotal'], $values['MemFree'], $values['Buffers'], $values['Cached'])) {
                $total = $values['MemTotal'];
                $free = $values['MemFree'] + $values['Buffers'] + $values['Cached'];
                $used = $total - $free;
                
                $memoryInfo['total'] = $total / 1024 / 1024; // MB
                $memoryInfo['used'] = $used / 1024 / 1024; // MB
                $memoryInfo['free'] = $free / 1024 / 1024; // MB
                $memoryInfo['usage_percent'] = ($used / $total) * 100;
                
                // Swap information
                if (isset($values['SwapTotal'], $values['SwapFree']) && $values['SwapTotal'] > 0) {
                    $swapTotal = $values['SwapTotal'];
                    $swapFree = $values['SwapFree'];
                    $swapUsed = $swapTotal - $swapFree;
                    
                    $memoryInfo['swap_total'] = $swapTotal / 1024 / 1024; // MB
                    $memoryInfo['swap_used'] = $swapUsed / 1024 / 1024; // MB
                    $memoryInfo['swap_free'] = $swapFree / 1024 / 1024; // MB
                    $memoryInfo['swap_usage_percent'] = ($swapUsed / $swapTotal) * 100;
                }
            }
        }
        
        return $memoryInfo;
    }
    
    /**
     * Get memory info on macOS.
     *
     * @return array
     */
    protected function getMacMemoryInfo(): array
    {
        $memoryInfo = [];
        
        try {
            // Get total physical memory
            $totalCmd = 'sysctl -n hw.memsize';
            $total = (int) shell_exec($totalCmd);
            
            // Use vm_stat for memory usage
            $vmstat = shell_exec('vm_stat');
            $lines = explode("\n", $vmstat);
            $values = [];
            
            foreach ($lines as $line) {
                if (preg_match('/^(\w.*?):\s+(\d+)/', $line, $matches)) {
                    $key = str_replace(' ', '_', strtolower($matches[1]));
                    $values[$key] = intval($matches[2]) * 4096; // Convert page count to bytes
                }
            }
            
            if ($total > 0 && isset($values['pages_free'], $values['pages_active'], $values['pages_inactive'], $values['pages_speculative'])) {
                $free = $values['pages_free'] + $values['pages_speculative'];
                $used = $total - $free;
                
                $memoryInfo['total'] = $total / 1024 / 1024; // MB
                $memoryInfo['used'] = $used / 1024 / 1024; // MB
                $memoryInfo['free'] = $free / 1024 / 1024; // MB
                $memoryInfo['usage_percent'] = ($used / $total) * 100;
                
                // Swap information
                $swapInfo = shell_exec('sysctl -n vm.swapusage');
                if (preg_match('/total = (\d+\.\d+)M.*used = (\d+\.\d+)M/s', $swapInfo, $matches)) {
                    $swapTotal = floatval($matches[1]);
                    $swapUsed = floatval($matches[2]);
                    $swapFree = $swapTotal - $swapUsed;
                    
                    $memoryInfo['swap_total'] = $swapTotal; // MB
                    $memoryInfo['swap_used'] = $swapUsed; // MB
                    $memoryInfo['swap_free'] = $swapFree; // MB
                    $memoryInfo['swap_usage_percent'] = ($swapUsed / $swapTotal) * 100;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting macOS memory info: ' . $e->getMessage());
        }
        
        return $memoryInfo;
    }
    
    /**
     * Get memory info on Windows.
     *
     * @return array
     */
    protected function getWindowsMemoryInfo(): array
    {
        $memoryInfo = [];
        
        try {
            // Use WMIC to get memory info
            $output = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
            $lines = explode("\n", $output);
            $values = [];
            
            foreach ($lines as $line) {
                if (preg_match('/^(\w+)=(\d+)$/', trim($line), $matches)) {
                    $values[$matches[1]] = intval($matches[2]) * 1024; // Convert KB to bytes
                }
            }
            
            if (isset($values['TotalVisibleMemorySize'], $values['FreePhysicalMemory'])) {
                $total = $values['TotalVisibleMemorySize'];
                $free = $values['FreePhysicalMemory'];
                $used = $total - $free;
                
                $memoryInfo['total'] = $total / 1024 / 1024; // MB
                $memoryInfo['used'] = $used / 1024 / 1024; // MB
                $memoryInfo['free'] = $free / 1024 / 1024; // MB
                $memoryInfo['usage_percent'] = ($used / $total) * 100;
                
                // Get swap information
                $swapOutput = shell_exec('wmic pagefile get AllocatedBaseSize,CurrentUsage /Value');
                $swapLines = explode("\n", $swapOutput);
                $swapValues = [];
                
                foreach ($swapLines as $line) {
                    if (preg_match('/^(\w+)=(\d+)$/', trim($line), $matches)) {
                        $swapValues[$matches[1]] = intval($matches[2]);
                    }
                }
                
                if (isset($swapValues['AllocatedBaseSize'], $swapValues['CurrentUsage'])) {
                    $swapTotal = $swapValues['AllocatedBaseSize'] * 1024 * 1024; // Convert MB to bytes
                    $swapUsed = $swapValues['CurrentUsage'] * 1024 * 1024; // Convert MB to bytes
                    $swapFree = $swapTotal - $swapUsed;
                    
                    $memoryInfo['swap_total'] = $swapTotal / 1024 / 1024; // MB
                    $memoryInfo['swap_used'] = $swapUsed / 1024 / 1024; // MB
                    $memoryInfo['swap_free'] = $swapFree / 1024 / 1024; // MB
                    $memoryInfo['swap_usage_percent'] = ($swapUsed / $swapTotal) * 100;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting Windows memory info: ' . $e->getMessage());
        }
        
        return $memoryInfo;
    }
    
    /**
     * Get simulated memory info for demonstration purposes.
     *
     * @return array
     */
    protected function getSimulatedMemoryInfo(): array
    {
        $this->warn('Using simulated memory usage for demonstration');
        
        $totalMB = 16 * 1024; // 16 GB
        $usagePercent = mt_rand(40, 85);
        $usedMB = ($totalMB * $usagePercent) / 100;
        $freeMB = $totalMB - $usedMB;
        
        $swapTotalMB = 4 * 1024; // 4 GB
        $swapUsagePercent = mt_rand(5, 30);
        $swapUsedMB = ($swapTotalMB * $swapUsagePercent) / 100;
        $swapFreeMB = $swapTotalMB - $swapUsedMB;
        
        return [
            'total' => $totalMB,
            'used' => $usedMB,
            'free' => $freeMB,
            'usage_percent' => $usagePercent,
            'swap_total' => $swapTotalMB,
            'swap_used' => $swapUsedMB,
            'swap_free' => $swapFreeMB,
            'swap_usage_percent' => $swapUsagePercent,
        ];
    }
}