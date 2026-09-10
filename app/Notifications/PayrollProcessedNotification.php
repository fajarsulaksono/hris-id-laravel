<?php

namespace App\Notifications;

use App\Models\Payroll\Payroll;
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
        return ['mail'];
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
