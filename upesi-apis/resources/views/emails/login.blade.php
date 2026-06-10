{{-- resources/views/emails/login.blade.php --}}
<x-mail::message>
# 🔐 Nouvelle connexion détectée

Bonjour {{ $user->first_name }} {{ $user->last_name }},

Votre compte a été connecté le **{{ $loginTime }}**.

**Détails :**
- 📍 IP : {{ $ip }}
- 📱 Appareil : {{ $device }}

@if($isNewDevice)
⚠️ **Ceci est un nouvel appareil.** Si ce n'est pas vous, changez immédiatement votre mot de passe.
@endif

<x-mail::button :url="url('app/password-reset/request')">
🔒 Changer mon mot de passe
</x-mail::button>

Si c'était vous, ignorez cet email.

À bientôt,<br>
{{ config('app.name') }}
</x-mail::message>
