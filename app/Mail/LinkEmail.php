<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class LinkEmail
 *
 * @package App\Mail
 */
class LinkEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Generated url for login
     *
     * @var string
     */
    public $link;

    /**
     * Create a new message instance.
     *
     * @param string $link
     */
    public function __construct($link)
    {
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.link');
    }
}
