<?php

namespace App\Notifications;

use App\Models\Lhp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LhpWorkflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Lhp $lhp,
        private readonly string $message,
        private readonly string $url
    ) {
    }

    /**
     * Create a new notification instance.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lhp_id' => $this->lhp->id,
            'judul_lhp' => $this->lhp->judul,
            'nomor_lhp' => $this->lhp->nomor_lhp,
            'status' => $this->lhp->status,
            'message' => $this->message,
            'url' => $this->url,
            'created_at' => now()->toISOString(),
        ];
    }
}
