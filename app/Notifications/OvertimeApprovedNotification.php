<?php

namespace App\Notifications;

use App\Models\Attendance\Overtime;
use App\Notifications\Channels\FcmChannel;
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
        return ['mail', 'database', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'overtime_approved',
            'title' => 'Lembur Disetujui',
            'message' => 'Pengajuan lembur Anda telah disetujui.',
            'related_id' => $this->overtime->getKey(),
        ];
    }

    /**
     * @return array{title: string, body: string, data: array<string, string>}
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Lembur Disetujui',
            'body' => 'Pengajuan lembur Anda telah disetujui.',
            'data' => [
                'type' => 'overtime_approved',
                'related_id' => (string) $this->overtime->getKey(),
            ],
        ];
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
