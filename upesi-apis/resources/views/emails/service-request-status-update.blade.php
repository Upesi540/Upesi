<x-mail::message>
@if($status === 'created')
# ✅ Demande envoyée
@elseif($status === 'in_progress')
# 🚀 Service en cours
@elseif($status === 'completed')
# 🎊 Service terminé
@elseif($status === 'rejected')
# ❌ Demande refusée
@endif

Bonjour {{ $serviceRequest->buyer->first_name }},

@if($status === 'created')
Votre demande pour **{{ $serviceRequest->serviceOffer->title }}** a bien été envoyée au prestataire.

Vous serez notifié dès qu'il répondra à votre demande.

@elseif($status === 'in_progress')
Bonne nouvelle ! Le prestataire a commencé à travailler sur votre demande **{{ $serviceRequest->serviceOffer->title }}**.

@elseif($status === 'completed')
Félicitations ! Le service **{{ $serviceRequest->serviceOffer->title }}** est terminé.

Merci d'avoir fait confiance à notre plateforme.

@elseif($status === 'rejected')
Le prestataire a malheureusement refusé votre demande pour **{{ $serviceRequest->serviceOffer->title }}**.

**Le montant de {{ number_format($serviceRequest->quoted_price, 0, ',', ' ') }} CFA a été remboursé** sur votre portefeuille.

N'hésitez pas à chercher un autre prestataire pour ce service.
@endif

<x-mail::button :url="url('/app/customer-service-requests')">
Suivre ma demande
</x-mail::button>

Merci de votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>
