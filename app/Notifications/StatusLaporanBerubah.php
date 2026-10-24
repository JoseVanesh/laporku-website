<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Http\Controllers\LaporanController;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusLaporanBerubah extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $laporan
     */
    public function __construct(
        public readonly array $laporan,
        public readonly string $statusBaru,
        public readonly string $oleh,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = LaporanController::STATUS_LABELS[$this->statusBaru] ?? $this->statusBaru;

        return (new MailMessage)
            ->subject('Update Laporan "'.$this->laporan['judul'].'" - Status: '.$label)
            ->greeting('Halo, '.($this->laporan['nama_penulis'] ?? 'Warga').'!')
            ->line('Laporan Anda dengan judul **"'.$this->laporan['judul'].'"** telah diperbarui.')
            ->line('Status terbaru: **'.$label.'**')
            ->line('Diperbarui oleh: '.$this->oleh)
            ->action('Lihat Laporan', route('laporan.show', $this->laporan['id']))
            ->line('Terima kasih telah menggunakan Laporku.');
    }
}
