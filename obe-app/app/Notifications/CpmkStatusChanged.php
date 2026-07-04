<?php

namespace App\Notifications;

use App\Models\ClassroomCpmk;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CpmkStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public ClassroomCpmk $cpmk,
        public string $action // 'approved' | 'rejected' | 'submitted'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $classroom = $this->cpmk->classroom;
        $msg = match($this->action) {
            'approved'  => "CPMK {$this->cpmk->code} untuk kelas {$classroom?->name} telah disetujui.",
            'rejected'  => "CPMK {$this->cpmk->code} untuk kelas {$classroom?->name} ditolak. Silakan revisi.",
            'submitted' => "CPMK {$this->cpmk->code} dari kelas {$classroom?->name} memerlukan persetujuan Anda.",
            default     => "Status CPMK {$this->cpmk->code} telah diperbarui.",
        };

        return [
            'cpmk_id'      => $this->cpmk->id,
            'classroom_id' => $this->cpmk->classroom_id,
            'action'       => $this->action,
            'message'      => $msg,
            'url'          => match($this->action) {
                'submitted' => route('kaprodi.dashboard'),
                default     => route('dosen.classrooms.show', $this->cpmk->classroom_id),
            },
        ];
    }
}
