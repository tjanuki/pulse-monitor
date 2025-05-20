<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification
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
        $alertType = strtoupper($this->alert->type);
        $metricName = optional($this->alert->statusMetric)->name ?? 'Unknown';
        $metricValue = optional($this->alert->statusMetric)->value ?? 'Unknown';
        
        return (new MailMessage)
            ->subject("[{$alertType}] Alert for {$nodeName} ({$nodeEnvironment})")
            ->greeting("Alert: {$this->alert->message}")
            ->line("Node: {$nodeName}")
            ->line("Environment: {$nodeEnvironment}")
            ->line("Metric: {$metricName}")
            ->line("Value: {$metricValue}")
            ->line("Status: {$alertType}")
            ->line("Time: " . $this->alert->created_at->format('Y-m-d H:i:s'))
            ->action('View Details', url("/dashboard/nodes/{$nodeId}"))
            ->line('Please investigate and resolve this issue as soon as possible.');
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
            'type' => $this->alert->type,
            'message' => $this->alert->message,
            'created_at' => $this->alert->created_at->toIso8601String(),
        ];
    }
}
