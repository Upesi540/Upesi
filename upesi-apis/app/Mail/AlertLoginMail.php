<?php
// app/Mail/AlertLoginMail.php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AlertLoginMail extends Mailable
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $ip,
        public string $device,
        public bool $isNewDevice = false
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Nouvelle connexion sur votre compte',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.login',
            with: [
                'loginTime' => now()->format('d/m/Y H:i:s'),
                'ip' => $this->ip,
                'device' => $this->device,
                'isNewDevice' => $this->isNewDevice
            ]
        );
    }
}
