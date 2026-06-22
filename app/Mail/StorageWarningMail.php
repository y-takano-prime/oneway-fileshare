<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StorageWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $targetUser;
    public $usedMb;
    public $capMb;
    public $percent;

    public function __construct(User $targetUser, $usedMb, $capMb, $percent)
    {
        $this->targetUser = $targetUser;
        $this->usedMb = $usedMb;
        $this->capMb = $capMb;
        $this->percent = $percent;
    }

    public function build()
    {
        return $this->subject('【AXON】ストレージ占有率が警告しきい値を超えました')
            ->text('emails.storage_warning');
    }
}
