<?php

namespace App\Notifications;

use App\Models\Attendance\Overtime;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OvertimeApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Overtime $overtime) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->overtime->overtime_date?->format((string) config('hris.format.date')) ?? '-';

        return (new MailMessage)
            ->subject('Lembur Anda Telah Disetujui')
            ->greeting('Halo '.$notifiable->full_name.',')
            ->line(sprintf(
                'Lembur tanggal %s (durasi %s jam) telah disetujui untuk Anda.',
                $date,
                number_format((float) $this->overtime->calculated_value, 1, ',', '.')
            ))
            ->line('Terima kasih atas kerja keras Anda.');
    }
}
