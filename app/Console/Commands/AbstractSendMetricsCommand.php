<?php

namespace App\Console\Commands;

use App\Models\StatusNode;
use App\Services\MetricsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

abstract class AbstractSendMetricsCommand extends Command
{
    /**
     * The metrics service instance.
     *
     * @var \App\Services\MetricsService
     */
    protected $metricsService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\MetricsService  $metricsService
     * @return void
     */
    public function __construct(MetricsService $metricsService)
    {
        parent::__construct();
        $this->metricsService = $metricsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $statusNode = $this->getStatusNode();
        
        if (!$statusNode) {
            $this->error('Status node not found');
            return Command::FAILURE;
        }
        
        try {
            $metrics = $this->collectMetrics();
            
            $this->info('Metrics collected: ' . count($metrics));
            
            foreach ($metrics as $metric) {
                $this->processMetric($statusNode, $metric);
            }
            
            $this->info('All metrics processed successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error collecting or processing metrics: ' . $e->getMessage());
            Log::error('Metrics collection failed: ' . $e->getMessage(), [
                'command' => get_class($this),
                'node_id' => $statusNode->id,
                'node_name' => $statusNode->name,
            ]);
            return Command::FAILURE;
        }
    }
    
    /**
     * Get the status node for which to send metrics.
     *
     * @return \App\Models\StatusNode|null
     */
    protected function getStatusNode()
    {
        $nodeId = $this->argument('node_id');
        
        return StatusNode::find($nodeId);
    }
    
    /**
     * Process a single metric.
     *
     * @param  \App\Models\StatusNode  $statusNode
     * @param  array  $metric
     * @return void
     */
    protected function processMetric(StatusNode $statusNode, array $metric)
    {
        $name = $metric['name'];
        $value = $metric['value'];
        $group = $metric['group'] ?? null;
        
        $this->info("Processing metric: {$name} = {$value}");
        
        $this->metricsService->processMetric($statusNode, $name, $value, $group);
    }
    
    /**
     * Collect metrics from the system.
     * This method should be implemented by subclasses.
     *
     * @return array Array of metrics, each with 'name', 'value', and optional 'group'
     */
    abstract protected function collectMetrics(): array;
}