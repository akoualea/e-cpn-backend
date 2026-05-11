
@component('mail::message')
# Votre médecin a été désigné !

Bonjour {{ $patientUser->prenom }},

Nous avons le plaisir de vous informer que votre médecin traitant pour le suivi de votre grossesse a été désigné sur notre plateforme.

Il s'agit du Dr. **{{ $proUser->prenom }} {{ $proUser->nom }}**.

Vous pouvez dès maintenant consulter les informations de votre médecin et accéder à votre suivi personnalisé sur votre tableau de bord.

@component('mail::button', ['url' => url('/patient/dashboard')])
Accéder au tableau de bord
@endcomponent

Nous vous souhaitons un excellent suivi !

Cordialement,
L'équipe Suivi de Grossesse
@endcomponent