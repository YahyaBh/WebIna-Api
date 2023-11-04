<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailNotificationAdmin extends Mailable
{
    use Queueable, SerializesModels;


    public $email;
    public $token;
    public $id;
    public $name;

    public $verificationURL;
    /**
     * Create a new message instance.
     */
    public function __construct($email, $token, $id , $name)
    {
        $this->email = $email;
        $this->token = $token;
        $this->id = $id;
        $this->name = $name;

        $this->verificationURL = 'http://localhost:3001' . '/verify-email/' . $this->token . '/' . $this->id . '/' . $this->email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to : $this->email,
            subject: 'Verify Email Notification Administration',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.verify-email-admin',
            with: [
                'link' => $this->verificationURL,
                'name' => $this->name
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
