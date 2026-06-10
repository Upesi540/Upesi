<?php
// app/Mail/NewOrderVendorMail.php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewOrderVendorMail extends Mailable
{
    use Queueable;

    public function __construct(
        public Order $order,
        public User $vendor,
        public string $vendorId
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🛒 Nouvelle commande #' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        // Calculer le total pour ce vendeur spécifique
        $vendorTotal = $this->order->items
            ->where('merchant_profile_id', $this->vendorId)
            ->sum('subtotal');

        return new Content(
            markdown: 'emails.new-order-vendor',
            with: [
                'order' => $this->order,
                'vendor' => $this->vendor,
                'vendorId' => $this->vendorId,
                'vendorTotal' => $vendorTotal,
            ]
        );
    }
}
