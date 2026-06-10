<?php
// app/Mail/OrderStatusUpdateMail.php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $status  // 'shipped' ou 'delivered'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->status === 'shipped'
            ? '🚚 Votre commande #' . $this->order->order_number . ' est en route !'
            : '🎉 Votre commande #' . $this->order->order_number . ' a été livrée !';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-status-update',
            with: [
                'order' => $this->order,
                'status' => $this->status,
            ]
        );
    }
}
