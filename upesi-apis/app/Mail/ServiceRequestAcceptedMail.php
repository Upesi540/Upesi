<?php
// app/Mail/ServiceRequestAcceptedMail.php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ServiceRequestAcceptedMail extends Mailable
{
    use Queueable;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $type  // 'accepted'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Demande acceptée #' . $this->serviceRequest->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service-request-accepted',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'type' => $this->type,
            ]
        );
    }
}
