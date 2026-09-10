<?php

namespace App\Notifications;

use App\Models\Payroll\Payroll;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(public Payroll $payroll) {}

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
            'type' => 'payroll_processed',
            'title' => 'Slip Gaji Tersedia',
            'message' => 'Slip gaji periode '.($this->payroll->period?->display ?? '-').' telah tersedia.',
            'related_id' => $this->payroll->getKey(),
        ];
    }

    /**
     * @return array{title: string, body: string, data: array<string, string>}
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Slip Gaji Tersedia',
            'body' => 'Slip gaji periode '.($this->payroll->period?->display ?? '-').' telah tersedia.',
            'data' => [
                'type' => 'payroll_processed',
                'related_id' => (string) $this->payroll->getKey(),
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $prefix = (string) config('hris.currency.prefix');
        $amount = number_format((float) $this->payroll->take_home_pay, (int) config('hris.currency.decimal_precision'), ',', '.');

        return (new MailMessage)
            ->subject('Slip Gaji Periode '.($this->payroll->period?->display ?? '-'))
            ->greeting('Halo '.$this->payroll->employee?->full_name.',')
            ->line(sprintf(
                'Penggajian periode %s telah selesai diproses. Take home pay Anda: %s%s.',
                $this->payroll->period?->display ?? '-',
                $prefix,
                $amount
            ))
            ->line('Slip gaji lengkap dapat dilihat melalui menu payroll.');
    }
}
