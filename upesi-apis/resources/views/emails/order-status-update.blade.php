{{-- resources/views/emails/order-status-update.blade.php --}}
<x-mail::message>
@if($status === 'shipped')
# 🚚 Votre commande est en route !
@else
# 🎉 Votre commande est livrée !
@endif

Bonjour {{ $order->buyer->first_name }},

@if($status === 'shipped')
Bonne nouvelle ! Votre commande **#{{ $order->order_number }}** a été expédiée.
@else
Félicitations ! Votre commande **#{{ $order->order_number }}** a été livrée avec succès.
@endif

**Montant total :** {{ number_format($order->total, 0, ',', ' ') }} CFA

<x-mail::button :url="url('/app/purchase-orders')">
Suivre ma commande
</x-mail::button>

Merci de votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>
