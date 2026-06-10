{{-- resources/views/emails/welcome.blade.php --}}
<x-mail::message>
# Bienvenue sur {{ config('app.name') }}

Bonjour {{ $user->first_name }} {{ $user->last_name }},

Votre compte a été créé avec succès.

**Vous êtes déjà connecté** à votre espace. Vous pouvez dès maintenant :

- 🛒 Découvrir nos produits
- 📦 Passer votre première commande
- 📊 Suivre les prix du marché

<x-mail::button :url="config('app.frontend_url') . '/user/profile'">
Accéder à mon espace
</x-mail::button>

Ou copiez ce lien dans votre navigateur :<br>
{{ config('app.frontend_url') }}/dashboard

À bientôt sur {{ config('app.name') }} !

</x-mail::message>
