<x-mail::message>
# 🔔 Nouvelle demande de service

Bonjour {{ $provider->first_name }} {{ $provider->last_name }},

**{{ $serviceRequest->buyer->first_name }} {{ $serviceRequest->buyer->last_name }}** a demandé votre service.

## 📋 Détails de la demande

**Service :** {{ $serviceRequest->serviceOffer->title }}
**Montant :** {{ number_format($serviceRequest->quoted_price, 0, ',', ' ') }} CFA
**Date souhaitée :** {{ $serviceRequest->scheduled_at ? $serviceRequest->scheduled_at->format('d/m/Y') : 'À convenir' }}

**Description :**
{{ $serviceRequest->description ?? 'Aucune description fournie' }}

<x-mail::button :url=" url('/app/merchant-service-requests')">
Voir la demande
</x-mail::button>

Vous avez deux options :
- ✅ **Accepter** : le service sera planifié
- ❌ **Refuser** : l'acheteur sera remboursé

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>
