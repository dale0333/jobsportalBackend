<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Helpers\AppHelper;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $data;

    public function __construct($title, $message, $data = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }

    public function build()
    {
        AppHelper::mailerConfig();

        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject($this->title)
            ->view('emails.generic-notification')
            ->with([
                'title'   => $this->title,
                'message' => $this->message,
                'data'    => $this->data,
            ]);
    }
}
