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

    public function __construct($code, $fileName)
    {
        $this->code = $code;
        $this->fileName = $fileName;
    }

    public function build()
    {
        return $this->subject('【oneway-fileshare】認証コードのご案内')
            ->view('emails.otp');
    }
}
