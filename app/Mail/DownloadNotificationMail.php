<?php

namespace App\Mail;

use App\Models\DownloadUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DownloadNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $downloadUrl;
    public $ipAddress;

    public function __construct(DownloadUrl $downloadUrl, $ipAddress)
    {
        $this->downloadUrl = $downloadUrl;
        $this->ipAddress = $ipAddress;
    }

    public function build()
    {
        return $this->subject('【AXON】ファイルがダウンロードされました')
            ->text('emails.download_notification');
    }
}
