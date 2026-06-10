<?php
// app/Mail/ServiceRequestStatusUpdateMail.php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ServiceRequestStatusUpdateMail extends Mailable
{
    use Queueable;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $status  // 'created', 'in_progress', 'completed', 'rejected'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->status) {
            'created' => '✅ Demande de service envoyée #' . $this->serviceRequest->request_number,
            'in_progress' => '🚀 Service en cours #' . $this->serviceRequest->request_number,
            'completed' => '🎊 Service terminé #' . $this->serviceRequest->request_number,
            'rejected' => '❌ Demande refusée #' . $this->serviceRequest->request_number,
            default => 'Mise à jour de votre demande #' . $this->serviceRequest->request_number,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service-request-status-update',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'status' => $this->status,
            ]
        );
    }
}
