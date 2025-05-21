# Pulse Monitor

A comprehensive server monitoring system built with Laravel, designed to track vital server metrics across multiple nodes with real-time alerting and visualization.

## Features

- **Multi-Node Monitoring**: Track multiple servers across different environments and regions
- **Key Metrics Collection**: Monitor CPU, memory, disk usage, and other system metrics
- **Threshold-Based Alerting**: Receive notifications when metrics exceed defined thresholds
- **Historical Data Analysis**: Track performance trends and compare metrics over time
- **Recommendations Engine**: Get actionable insights to resolve performance issues
- **Real-Time Dashboard**: Visualize node status and metrics in an intuitive interface

## Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/pulse-monitor.git
cd pulse-monitor

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file and configure your database connection
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed

# Build frontend assets
npm run build
```

## Usage

### Starting the Application

```bash
# Start the development server
php artisan serve

# Run all development services (server, queue listener, logs, vite)
composer dev
```

### Registering Status Nodes

```bash
# Register a new node with environment and region
php artisan node:register web-server-1 production us-east
```

### Collecting Metrics

Metrics can be collected using the built-in collectors or by implementing custom collectors. You need to provide the node ID as an argument:

```bash
# First register a node to get an ID
php artisan node:register my-server

# Collect CPU metrics (replace 1 with your node ID)
php artisan metrics:collect-cpu 1

# Collect memory metrics (replace 1 with your node ID)
php artisan metrics:collect-memory 1

# Collect disk metrics (replace 1 with your node ID)
php artisan metrics:collect-disk 1
```

### API Integration

Send metrics to the monitoring system from remote servers:

```php
// Example API call to send metrics
$response = Http::withToken($apiKey)
    ->post('https://your-monitor-url.com/api/metrics', [
        'node_id' => $nodeId,
        'metrics' => [
            [
                'name' => 'cpu_usage',
                'value' => 45.2,
                'group' => 'system',
                'metadata' => ['cores' => 8]
            ],
            // Add more metrics as needed
        ]
    ]);
```

## Configuration

### Alert Thresholds

Configure warning and critical thresholds for different metrics through the web interface or using the database seeder:

```php
// database/seeders/ThresholdConfigurationsSeeder.php
ThresholdConfiguration::create([
    'metric_name' => 'cpu_usage',
    'warning_threshold' => 70,
    'critical_threshold' => 90,
]);
```

## Testing

```bash
# Run all tests
php artisan test
# or
composer test

# Run a specific test
php artisan test --filter=TestName
```

## Architecture

This application follows Laravel's standard architecture:

- **Models**: Define database relationships and business logic
- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain core business logic for metrics processing and alerting
- **Commands**: Implement metric collection and node registration
- **Livewire Components**: Provide dynamic UI for the dashboard

## Implementation Phases

The application was built in the following phases:

1. **Foundation**: Database structure, authentication system
2. **Core Services**: Metrics service, command structure
3. **API Layer**: Controllers, routes, validation
4. **Visualization**: Dashboard components, Pulse integration
5. **Advanced Features**: Alerting system, historical data analysis

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).