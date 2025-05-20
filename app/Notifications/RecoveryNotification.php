<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecoveryNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected Alert $alert;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $nodeId = $this->alert->status_node_id;
        $nodeName = $this->alert->statusNode->name;
        $nodeEnvironment = $this->alert->statusNode->environment;
        $metricName = optional($this->alert->statusMetric)->name ?? 'Unknown';
        $metricValue = optional($this->alert->statusMetric)->value ?? 'Unknown';
        
        return (new MailMessage)
            ->subject("[RECOVERY] Alert Resolved for {$nodeName} ({$nodeEnvironment})")
            ->greeting("Recovery: {$this->alert->message}")
            ->line("Node: {$nodeName}")
            ->line("Environment: {$nodeEnvironment}")
            ->line("Metric: {$metricName}")
            ->line("Value: {$metricValue}")
            ->line("Status: RECOVERED")
            ->line("Time: " . $this->alert->created_at->format('Y-m-d H:i:s'))
            ->action('View Details', url("/dashboard/nodes/{$nodeId}"))
            ->line("The issue has been resolved automatically.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->alert->id,
            'node_id' => $this->alert->status_node_id,
            'node_name' => $this->alert->statusNode->name,
            'metric_id' => $this->alert->status_metric_id,
            'metric_name' => optional($this->alert->statusMetric)->name,
            'type' => 'recovery',
            'message' => $this->alert->message,
            'created_at' => $this->alert->created_at->toIso8601String(),
        ];
    }
}
