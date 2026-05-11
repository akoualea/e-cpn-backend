@component('mail::message')
# Nouveau patient attribué !

Bonjour {{ $proUser->prenom }},

Un nouveau patient, **{{ $patientUser->prenom }} {{ $patientUser->nom }}**, vous a été attribué pour son suivi de grossesse.

Vous pouvez consulter son dossier dès maintenant sur votre tableau de bord.

@component('mail::button', ['url' => url('/medecin/dashboard')])
Accéder au tableau de bord
@endcomponent

Merci pour votre engagement.

Cordialement,
L'équipe Suivi de Grossesse
@endcomponent