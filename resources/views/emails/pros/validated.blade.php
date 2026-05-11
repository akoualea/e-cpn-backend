@component('mail::message')
# Votre compte professionnel est actif !

Bonjour {{ $user->prenom }},

Nous sommes ravis de vous informer que votre compte professionnel de santé sur notre plateforme "Suivi de Grossesse" a été validé.

Vous pouvez dès maintenant vous connecter et commencer à gérer le suivi de vos patientes.

@component('mail::button', ['url' => url('/medecin/login')])
Se connecter
@endcomponent

Merci de faire partie de notre communauté !

Cordialement,
L'équipe Suivi de Grossesse
@endcomponent