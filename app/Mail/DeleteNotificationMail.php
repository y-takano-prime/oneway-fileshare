<?php

namespace App\Mail;

use App\Models\DownloadUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeleteNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $downloadUrl;
    public $deleteDate;

    public function __construct(DownloadUrl $downloadUrl, $deleteDate)
    {
        $this->downloadUrl = $downloadUrl;
        $this->deleteDate = $deleteDate;
    }

    public function build()
    {
        return $this->subject('【AXON】ファイルが自動削除される予定です')
            ->text('emails.delete_notification');
    }
}
