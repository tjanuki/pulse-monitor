<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\Recommendation;
use App\Models\User;
use App\Notifications\AlertNotification;
use App\Notifications\RecoveryNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;

class AlertsService
{
    /**
     * Generate alerts based on a status metric
     *
     * @param StatusMetric $metric
     * @return Alert|null
     */
    public function processMetric(StatusMetric $metric): ?Alert
    {
        // Don't generate alerts for metrics that are normal
        if ($metric->status === 'normal') {
            // Check if we need to generate a recovery alert
            return $this->handleRecovery($metric);
        }

        // Check if we already have an unresolved alert for this metric
        $existingAlert = Alert::where('status_metric_id', $metric->id)
            ->where('status_node_id', $metric->status_node_id)
            ->whereNull('resolved_at')
            ->first();

        if ($existingAlert) {
            return null; // We already have an alert for this issue
        }

        // Create a new alert
        $alert = Alert::create([
            'status_node_id' => $metric->status_node_id,
            'status_metric_id' => $metric->id,
            'type' => $metric->status, // 'warning' or 'critical'
            'message' => "Metric {$metric->name} is in {$metric->status} state: {$metric->value}",
            'context' => [
                'metric' => $metric->toArray(),
                'recommendations' => $this->getRecommendations($metric)->toArray(),
            ],
        ]);

        // Send notifications
        $this->sendNotifications($alert);

        return $alert;
    }

    /**
     * Handle recovery for a metric
     *
     * @param StatusMetric $metric
     * @return Alert|null
     */
    protected function handleRecovery(StatusMetric $metric): ?Alert
    {
        // Find the most recent unresolved alert for this metric type
        $existingAlert = Alert::where('status_node_id', $metric->status_node_id)
            ->whereHas('statusMetric', function ($query) use ($metric) {
                $query->where('name', $metric->name);
            })
            ->whereNull('resolved_at')
            ->first();

        if (!$existingAlert) {
            return null; // No alert to recover from
        }

        // Mark the alert as resolved
        $existingAlert->resolved_at = now();
        $existingAlert->save();

        // Create a recovery alert
        $alert = Alert::create([
            'status_node_id' => $metric->status_node_id,
            'status_metric_id' => $metric->id,
            'type' => 'recovery',
            'message' => "Metric {$metric->name} has recovered and is now normal: {$metric->value}",
            'context' => [
                'previous_alert' => $existingAlert->toArray(),
                'metric' => $metric->toArray(),
            ],
        ]);

        // Send recovery notifications
        $this->sendRecoveryNotifications($alert);

        return $alert;
    }

    /**
     * Get recommendations for a metric
     *
     * @param StatusMetric $metric
     * @return Collection
     */
    public function getRecommendations(StatusMetric $metric): Collection
    {
        return Recommendation::where('is_active', true)
            ->get()
            ->filter(function ($recommendation) use ($metric) {
                return $recommendation->matchesMetric($metric->name, $metric->value);
            });
    }

    /**
     * Send notifications for an alert
     *
     * @param Alert $alert
     * @return void
     */
    protected function sendNotifications(Alert $alert): void
    {
        // Log the alert
        logger()->info("Alert notification: {$alert->message}");

        // Send notifications to all admin users
        $users = User::all(); // In a real app, you might filter for admins only
        
        if ($users->isNotEmpty()) {
            Notification::send($users, new AlertNotification($alert));
        }
        
        // You can add more notification channels here
        // Example: SMS, Slack, etc.
    }

    /**
     * Send recovery notifications
     *
     * @param Alert $alert
     * @return void
     */
    protected function sendRecoveryNotifications(Alert $alert): void
    {
        // Log the recovery
        logger()->info("Recovery notification: {$alert->message}");

        // Send notifications to all admin users
        $users = User::all(); // In a real app, you might filter for admins only
        
        if ($users->isNotEmpty()) {
            Notification::send($users, new RecoveryNotification($alert));
        }
        
        // You can add more notification channels here
        // Example: SMS, Slack, etc.
    }

    /**
     * Get all unresolved alerts
     *
     * @return Collection
     */
    public function getUnresolvedAlerts(): Collection
    {
        return Alert::unresolved()
            ->with(['statusNode', 'statusMetric'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all unresolved alerts for a specific node
     *
     * @param int $nodeId
     * @return Collection
     */
    public function getUnresolvedAlertsForNode(int $nodeId): Collection
    {
        return Alert::unresolved()
            ->where('status_node_id', $nodeId)
            ->with(['statusNode', 'statusMetric'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Resolve an alert
     *
     * @param Alert $alert
     * @return void
     */
    public function resolveAlert(Alert $alert): void
    {
        $alert->resolved_at = now();
        $alert->save();
    }
}