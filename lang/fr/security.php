<?php

return [
    'title' => 'Paramètres de sécurité',
    'password' => [
        'heading' => 'Mettre à jour le mot de passe',
        'description' => 'Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester en sécurité',
        'current' => 'Mot de passe actuel',
        'new' => 'Nouveau mot de passe',
        'confirm' => 'Confirmer le mot de passe',
        'save' => 'Enregistrer le mot de passe',
    ],
    '2fa' => [
        'title' => 'Authentification à deux facteurs',
        'description' => 'Gérez vos paramètres d\'authentification à deux facteurs',
        'help_disabled' => 'Lorsque vous activez l\'authentification à deux facteurs, vous serez invité à saisir un code PIN sécurisé lors de la connexion. Ce code PIN peut être récupéré à partir d\'une application compatible TOTP sur votre téléphone.',
        'help_enabled' => 'Vous serez invité à saisir un code PIN aléatoire et sécurisé lors de la connexion, que vous pourrez récupérer à partir de l\'application compatible TOTP sur votre téléphone.',
        'continue' => 'Continuer la configuration',
        'enable' => 'Activer la 2FA',
        'disable' => 'Désactiver la 2FA',
    ],
];
