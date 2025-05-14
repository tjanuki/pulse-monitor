<?php

namespace App\Console\Commands;

use App\Models\StatusNodeRegistration;
use Illuminate\Console\Command;

class RegisterStatusNode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'node:register 
                            {name : The name of the status node}
                            {environment=production : The environment (e.g., production, staging, development)}
                            {region=us-west : The region where the node is located}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a new status node and generate an API key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $environment = $this->argument('environment');
        $region = $this->argument('region');

        $this->info("Registering new status node: {$name}");
        $this->info("Environment: {$environment}");
        $this->info("Region: {$region}");

        try {
            // Register the node and get the API key
            $node = StatusNodeRegistration::register($name, $environment, $region);

            $this->info('Status node registered successfully!');
            $this->info('');
            $this->info('Node Details:');
            $this->info('-----------------');
            $this->info("ID: {$node->id}");
            $this->info("Name: {$node->name}");
            $this->info("Environment: {$node->environment}");
            $this->info("Region: {$node->region}");
            $this->info("API Key: {$node->api_key}");
            $this->info('');
            $this->warn('IMPORTANT: Save this API key as it will not be shown again!');
            $this->info('');
            $this->info('Usage Example:');
            $this->info("curl -X POST https://your-app.com/api/metrics \\");
            $this->info("  -H \"X-API-Key: {$node->api_key}\" \\");
            $this->info('  -H "Content-Type: application/json" \\');
            $this->info('  -d \'{"name": "cpu_usage", "group": "system", "value": 45.2}\'');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error registering status node: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
