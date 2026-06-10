<x-mail::message>
# 🛒 Nouvelle commande !

Bonjour {{ $vendor->first_name }} {{ $vendor->last_name }},

Vous avez reçu une nouvelle commande.

## 📋 Commande #{{ $order->order_number }}

**Date :** {{ $order->created_at->format('d/m/Y H:i') }}
**Montant total :** {{ number_format($vendorTotal, 0, ',', ' ') }} CFA

### Détails des produits :

@foreach($order->items->where('merchant_profile_id', $vendorId) as $item)
- **{{ $item->product_name }}** x {{ $item->quantity }} = {{ number_format($item->subtotal, 0, ',', ' ') }} CFA
@endforeach

<x-mail::button :url="url('/app/sale-orders')">
Voir la commande
</x-mail::button>

À bientôt,<br>
{{ config('app.name') }}
</x-mail::message>
