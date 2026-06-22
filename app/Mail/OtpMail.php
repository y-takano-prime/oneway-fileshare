<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $fileName;
    public $senderEmail;
    public $senderName;

    public function __construct($code, $fileName, $senderEmail = null, $senderName = null)
    {
        $this->code = $code;
        $this->fileName = $fileName;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function build()
    {
        $mail = $this->subject('【AXON】認証コードのご案内')
            ->view('emails.otp');

        if ($this->senderEmail) {
            $mail->from($this->senderEmail, $this->senderName);
        }

        return $mail;
    }
}
