<?php
// app/Mail/OrderConfirmedBuyerMail.php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderConfirmedBuyerMail extends Mailable
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $type  // 'created' ou 'confirmed'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->type === 'created'
            ? '✅ Commande confirmée #' . $this->order->order_number
            : '📦 Votre commande #' . $this->order->order_number . ' est confirmée';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmed-buyer',
            with: [
                'order' => $this->order,
                'type' => $this->type,
            ]
        );
    }
}
