<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',

    /*
    |--------------------------------------------------------------------------
    | Authentication Page 
    |--------------------------------------------------------------------------
    */

    "sign_in" => [
        "title" => "Connectez-vous à votre compte",
        "subtitle" => "Entrez votre email et votre mot de passe ci-dessous pour vous connecter",
        "button" => "Se connecter"
    ],

    "register" => [
        "title" => "Créez un compte",
        "subtitle" => "Entrez votre email et votre mot de passe ci-dessous pour vous inscrire",
        "button" => "S'inscrire"
    ],

    "confirm_password" => [
        "title"=> "Confirmer votre mot de passe",
        "description" => "Ceci est une zone sécurisée de l'application. Veuillez confirmer votre mot de passe avant de continuer.",
        "button" => "Confirmer le mot de passe"
    ],

    "form"=> [
        "name" => "Nom complet",
        "email" => "Adresse email",
        "password" => "Mot de passe",
        "confirm_password" => "Confirmer le mot de passe",
        "remember_me" => "Se souvenir de moi",
        "sign_in_button" => "Se connecter",
        "forgot_password" => "Mot de passe oublié ?",
        "role" => "Rôle",
        "role_student" => "Étudiant",
        "role_teacher" => "Enseignant"
    ],

    "placeholder" => [
        "name" => "Nom complet"
    ],

    "forgot_password" => [
        "title" => "Mot de passe oublié",
        "description" => "Entrez votre email pour recevoir un lien de réinitialisation",
        "button" => "Envoyer le lien de réinitialisation",
        "return_to_login" => "Ou, revenir à",
    ],

    "reset_password" => [
        "title" => "Réinitialiser le mot de passe",
        "description" => "Veuillez entrer votre nouveau mot de passe ci-dessous",
        "button" => "Réinitialiser le mot de passe",
    ],

    "links" => [
        "sign_up" => "Vous n'avez pas de compte ?",
        "sign_up_link" => "S'inscrire",
        "sign_in" => "Vous avez déjà un compte ?",
        "sign_in_link" => "Se connecter"
    ]
];
