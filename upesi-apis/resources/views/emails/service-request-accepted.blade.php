<x-mail::message>
# 🎉 Votre demande a été acceptée !

Bonjour {{ $serviceRequest->buyer->first_name }},

**Bonne nouvelle !** Le prestataire a accepté votre demande pour **{{ $serviceRequest->serviceOffer->title }}**.

## 📋 Récapitulatif

**Service :** {{ $serviceRequest->serviceOffer->title }}
**Prestataire :** {{ $serviceRequest->merchantProfile->shop_name ?? 'Prestataire' }}
**Montant :** {{ number_format($serviceRequest->quoted_price, 0, ',', ' ') }} CFA
**Date :** {{ $serviceRequest->scheduled_at ? $serviceRequest->scheduled_at->format('d/m/Y') : 'À convenir' }}

Le prestataire va maintenant organiser la prestation. Vous pourrez suivre l'avancement depuis votre espace.

<x-mail::button :url="url('/app/customer-service-requests')">
Suivre ma demande
</x-mail::button>

À bientôt,<br>
{{ config('app.name') }}
</x-mail::message>
