<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileShareNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $senderEmail;
    public $senderName;

    public function __construct($body, $senderEmail = null, $senderName = null)
    {
        $this->body = $body;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function build()
    {
        $mail = $this->subject('ファイルのご案内')
            ->text('emails.file_share_notification');

        if ($this->senderEmail) {
            $mail->from($this->senderEmail, $this->senderName);
        }

        return $mail;
    }
}
