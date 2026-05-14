<?php

return [
    'title' => 'Gestion des Utilisateurs',
    'description' => 'Gérer les utilisateurs inscrits sur la plateforme.',
    'search' => 'Rechercher un nom ou un e-mail...',
    'empty' => 'Aucun utilisateur trouvé.',
    'filter' => [
        'roles' => [
            'all' => 'Tous les Rôles',
            'admin' => 'Administrateur',
            'teacher' => 'Enseignant',
            'student' => 'Étudiant',
        ],
        'statuses' => [
            'all' => 'Tous les Statuts',
            'approved' => 'Approuvé',
            'unapproved' => 'Non Approuvé',
        ],
    ],
    'table' => [
        'name' => 'Nom',
        'email' => 'E-mail',
        'role' => 'Rôle',
        'status' => 'Statut',
        'joined' => 'Inscrit le',
        'actions' => 'Actions',
    ],
    'actions' => [
        'revoke' => 'Révoquer l\'Approbation',
        'edit' => 'Modifier',
        'approve' => 'Approuver',
        'delete' => 'Supprimer',
    ],
    'createDialog' => [
        'title' => 'Ajouter un Utilisateur',
        'description' => 'Ajouter un nouvel utilisateur au système.',
        'create' => 'Ajouter un Utilisateur',
        'creating' => 'Enregistrement...',
        'close' => 'Annuler',
        'name' => 'Nom',
        'email' => 'E-mail',
        'password' => 'Mot de passe',
        'role' => 'Rôle',
        'roles' => [
            'admin' => 'Administrateur',
            'teacher' => 'Enseignant',
            'student' => 'Étudiant',
        ],
    ],
    'editDialog' => [
        'title' => 'Modifier l\'Utilisateur',
        'description' => 'Modifiez les données de l\'utilisateur ici.',
        'update' => 'Enregistrer les Modifications',
        'updating' => 'Enregistrement...',
        'close' => 'Annuler',
        'passwordNote' => 'Laisser vide si inchangé',
    ],
    'deleteDialog' => [
        'title' => 'Êtes-vous sûr ?',
        'description' => 'Cette action est irréversible. Cela supprimera définitivement l\'utilisateur du système.',
        'cancel' => 'Annuler',
        'confirm' => 'Supprimer',
    ],
];
