<x-mail::message>
# ✅ Commande confirmée

Bonjour {{ $order->buyer->first_name }},

Votre commande **#{{ $order->order_number }}** a été confirmée.

**Montant total :** {{ number_format($order->total, 0, ',', ' ') }} CFA

Nous vous tiendrons informé de l'évolution de votre commande.

<x-mail::button :url="url('/app/purchase-orders')">
Suivre ma commande
</x-mail::button>

Merci de votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>
