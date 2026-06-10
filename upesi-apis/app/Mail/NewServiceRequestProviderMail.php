<?php
// app/Mail/NewServiceRequestProviderMail.php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewServiceRequestProviderMail extends Mailable
{
    use Queueable;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public User $provider  // ← Assure-toi que $provider est bien passé
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Nouvelle demande de service #' . $this->serviceRequest->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-service-request-provider',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'provider' => $this->provider,  // ← PASSE BIEN $provider
                'buyer' => $this->serviceRequest->buyer,
            ]
        );
    }
}
