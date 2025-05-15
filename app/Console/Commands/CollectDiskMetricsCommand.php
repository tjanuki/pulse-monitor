<?php

namespace App\Console\Commands;

use App\Services\MetricsService;
use Illuminate\Support\Facades\Log;

class CollectDiskMetricsCommand extends AbstractSendMetricsCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect-disk
                            {node_id : The ID of the status node to collect metrics for}
                            {--paths=* : Specific disk paths to monitor (default: all mounted filesystems)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect disk usage metrics and send them to the status node';

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
     * Collect disk metrics from the system.
     *
     * @return array
     */
    protected function collectMetrics(): array
    {
        $metrics = [];
        $paths = $this->option('paths');
        
        try {
            // If specific paths were provided, use them
            if (!empty($paths)) {
                foreach ($paths as $path) {
                    $diskInfo = $this->getDiskInfo($path);
                    $this->addDiskMetrics($metrics, $diskInfo, basename($path));
                }
            } else {
                // Otherwise collect all mounted filesystems
                $mountPoints = $this->getMountPoints();
                
                foreach ($mountPoints as $mountPoint) {
                    $diskInfo = $this->getDiskInfo($mountPoint);
                    
                    // Create a safe name for the mount point
                    $name = $this->getDiskName($mountPoint);
                    
                    $this->addDiskMetrics($metrics, $diskInfo, $name);
                }
            }
            
            // Add IO stats if available
            $ioStats = $this->getDiskIOStats();
            if (!empty($ioStats)) {
                foreach ($ioStats as $device => $stats) {
                    foreach ($stats as $statName => $value) {
                        $metrics[] = [
                            'name' => "disk_{$device}_{$statName}",
                            'group' => 'disk_io',
                            'value' => $value,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error collecting disk metrics: ' . $e->getMessage());
            $this->error('Error collecting disk metrics: ' . $e->getMessage());
        }
        
        return $metrics;
    }
    
    /**
     * Add disk metrics to the metrics array.
     *
     * @param array $metrics Metrics array to add to
     * @param array $diskInfo Disk information
     * @param string $name The name/identifier for the disk
     * @return void
     */
    protected function addDiskMetrics(array &$metrics, array $diskInfo, string $name): void
    {
        if (isset($diskInfo['usage_percent'])) {
            $metrics[] = [
                'name' => "disk_{$name}_usage",
                'group' => 'disk',
                'value' => $diskInfo['usage_percent'],
            ];
        }
        
        if (isset($diskInfo['total_gb'])) {
            $metrics[] = [
                'name' => "disk_{$name}_total",
                'group' => 'disk',
                'value' => $diskInfo['total_gb'],
            ];
        }
        
        if (isset($diskInfo['used_gb'])) {
            $metrics[] = [
                'name' => "disk_{$name}_used",
                'group' => 'disk',
                'value' => $diskInfo['used_gb'],
            ];
        }
        
        if (isset($diskInfo['free_gb'])) {
            $metrics[] = [
                'name' => "disk_{$name}_free",
                'group' => 'disk',
                'value' => $diskInfo['free_gb'],
            ];
        }
        
        if (isset($diskInfo['inodes_usage_percent'])) {
            $metrics[] = [
                'name' => "disk_{$name}_inodes_usage",
                'group' => 'disk',
                'value' => $diskInfo['inodes_usage_percent'],
            ];
        }
    }
    
    /**
     * Get a clean disk name from a mount point.
     *
     * @param string $mountPoint
     * @return string
     */
    protected function getDiskName(string $mountPoint): string
    {
        // Format the mount point into a usable name
        $name = trim($mountPoint, '/');
        
        if (empty($name)) {
            return 'root';
        }
        
        // Replace problematic characters
        $name = preg_replace('/[^a-z0-9_]/i', '_', $name);
        return strtolower($name);
    }
    
    /**
     * Get disk information for a specific path.
     *
     * @param string $path The path to check
     * @return array
     */
    protected function getDiskInfo(string $path): array
    {
        $diskInfo = [];
        
        if (!file_exists($path)) {
            $this->warn("Path does not exist: {$path}");
            return $diskInfo;
        }
        
        try {
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            
            // Convert to GB for better readability
            $diskInfo['total_gb'] = round($total / 1024 / 1024 / 1024, 2);
            $diskInfo['used_gb'] = round($used / 1024 / 1024 / 1024, 2);
            $diskInfo['free_gb'] = round($free / 1024 / 1024 / 1024, 2);
            $diskInfo['usage_percent'] = ($used / $total) * 100;
            
            // Get inode usage if on Unix-like system
            if (PHP_OS !== 'WINNT') {
                $this->getInodeUsage($path, $diskInfo);
            }
        } catch (\Exception $e) {
            $this->warn("Could not get disk info for {$path}: " . $e->getMessage());
        }
        
        return $diskInfo;
    }
    
    /**
     * Get inode usage for a mount point (Unix-like systems only).
     *
     * @param string $path
     * @param array $diskInfo
     * @return void
     */
    protected function getInodeUsage(string $path, array &$diskInfo): void
    {
        try {
            // Use df command to get inode usage
            $cmd = "df -i " . escapeshellarg($path) . " | tail -1";
            $output = shell_exec($cmd);
            
            if (preg_match('/(\d+)\s+(\d+)\s+(\d+)\s+(\d+)%/', $output, $matches)) {
                $diskInfo['inodes_total'] = intval($matches[1]);
                $diskInfo['inodes_used'] = intval($matches[2]);
                $diskInfo['inodes_free'] = intval($matches[3]);
                $diskInfo['inodes_usage_percent'] = intval($matches[4]);
            }
        } catch (\Exception $e) {
            // Silently fail for inode stats - they're not critical
        }
    }
    
    /**
     * Get all mounted filesystems.
     *
     * @return array
     */
    protected function getMountPoints(): array
    {
        $mountPoints = [];
        
        if (PHP_OS === 'WINNT') {
            // Windows - get logical drives
            if (function_exists('exec')) {
                exec('wmic logicaldisk get caption', $output);
                foreach ($output as $line) {
                    $drive = trim($line);
                    if (preg_match('/^[A-Z]:$/', $drive)) {
                        $mountPoints[] = $drive . '\\';
                    }
                }
            }
            
            // If we couldn't get drives, use some defaults
            if (empty($mountPoints)) {
                $mountPoints = ['C:\\'];
            }
        } else {
            // Unix-like - use df command
            if (function_exists('exec')) {
                exec("df -P | awk '{print $6}' | tail -n +2", $output);
                $mountPoints = array_filter($output, function ($path) {
                    // Skip some virtual filesystems
                    return !preg_match('!^/(dev|proc|sys|run|snap)!', $path);
                });
            }
            
            // If we couldn't get mount points, use root
            if (empty($mountPoints)) {
                $mountPoints = ['/'];
            }
        }
        
        return $mountPoints;
    }
    
    /**
     * Get disk I/O statistics (Linux only).
     *
     * @return array
     */
    protected function getDiskIOStats(): array
    {
        $ioStats = [];
        
        if (PHP_OS === 'Linux' && is_file('/proc/diskstats') && function_exists('exec')) {
            try {
                // Read disk I/O stats from /proc/diskstats
                exec("cat /proc/diskstats | awk '{print $3,$6,$10}'", $output);
                
                foreach ($output as $line) {
                    $parts = explode(' ', $line);
                    if (count($parts) === 3 && !preg_match('/^(loop|ram)/', $parts[0])) {
                        $device = $parts[0];
                        $reads = intval($parts[1]);
                        $writes = intval($parts[2]);
                        
                        $ioStats[$device] = [
                            'reads' => $reads,
                            'writes' => $writes,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Silently fail, IO stats are optional
            }
        }
        
        return $ioStats;
    }
}