<?php

return [
    // Admin
    'title' => 'Gestion des Classes',
    'description' => 'Gérer les classes disponibles sur la plateforme.',
    'search' => 'Rechercher un nom ou un code de classe...',
    'empty' => 'Aucune classe trouvée.',
    'lookup' => 'Sélectionner l\'enseignant...',
    'lookupEmpty' => 'Aucun enseignant trouvé',
    'lookupPlaceholder' => 'Rechercher un enseignant...',
    'table' => [
        'code' => 'Code',
        'name' => 'Nom de la classe',
        'teacher' => 'Enseignant',
        'created' => 'Créé le',
        'actions' => 'Actions',
        'openMenu' => 'Ouvrir le menu',
    ],
    'actions' => [
        'viewStudents' => 'Voir les étudiants',
        'delete' => 'Supprimer',
    ],
    'enrollmentDialog' => [
        'title' => 'Données des étudiants - :name',
        'description' => 'Liste des étudiants inscrits dans cette classe.',
        'loading' => 'Chargement des données...',
        'empty' => 'Aucun étudiant dans cette classe.',
    ],
    'deleteDialog' => [
        'title' => 'Êtes-vous sûr ?',
        'description' => 'Cette action est irréversible. Cela supprimera définitivement cette classe. Note : Les classes avec des devoirs actifs ne peuvent pas être supprimées.',
        'cancel' => 'Annuler',
        'confirm' => 'Supprimer',
    ],

    // Teacher
    'teacher' => [
        'title' => 'Ma Classe',
        'list' => 'Liste des Classes',
        'description' => 'Gérez vos classes et vos enseignements.',
        'new' => 'Nouvelle Classe',
        'empty' => 'Vous n\'avez pas encore de classes.',
        'createFirst' => 'Créez votre première classe',
        'form' => [
            'createTitle' => 'Créer une nouvelle classe',
            'editTitle' => 'Modifier la classe',
            'createDesc' => 'Ajoutez une nouvelle classe à votre liste. Le code de classe sera généré automatiquement.',
            'editDesc' => 'Modifiez les informations de votre classe ici.',
            'name' => 'Nom de la classe',
            'namePlaceholder' => 'ex: Mathématiques de base',
            'description' => 'Description (Optionnel)',
            'descriptionPlaceholder' => 'Entrez la description de la classe...',
            'save' => 'Enregistrer',
            'saving' => 'Enregistrement...',
            'cancel' => 'Annuler',
        ],
        'deleteDialog' => [
            'title' => 'Supprimer la classe ?',
            'description' => 'Cette action est irréversible. Les classes avec des devoirs actifs ne peuvent pas être supprimées.',
            'cancel' => 'Annuler',
            'confirm' => 'Oui, Supprimer',
        ],
        'detailTitle' => 'Détail de la classe :name',
        'stats' => [
            'students' => 'Total des étudiants',
            'tasks' => 'Devoirs actifs',
        ],
        'studentList' => 'Liste des étudiants inscrits',
        'table' => [
            'studentName' => 'Nom de l\'étudiant',
            'email' => 'E-mail',
            'empty' => 'Aucun étudiant inscrit dans cette classe pour le moment.',
        ],
    ],
];
