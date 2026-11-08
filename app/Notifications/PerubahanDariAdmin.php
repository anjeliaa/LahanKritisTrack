<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/** Pemberitahuan ke Petugas: Admin mengubah kegiatan atau monitoring miliknya. */
class PerubahanDariAdmin extends Notification
{
    /**
     * @param  string  $jenis      "kegiatan" | "monitoring"
     * @param  array<int, array{label: string, lama: ?string, baru: ?string}>  $perubahan
     */
    public function __construct(
        public string $jenis,
        public string $kegiatan,
        public string $oleh,
        public array $perubahan,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'jenis'     => $this->jenis,
            'kegiatan'  => $this->kegiatan,
            'oleh'      => $this->oleh,
            'perubahan' => $this->perubahan,
        ];
    }
}
