<?php

return [
  "admin" => [
    "classroom" => [
      "deleted" => "Classe supprimée avec succès.",
      "activeTasks" => "La classe ne peut pas être supprimée car elle contient des tâches actives.",
    ],
    "task" => [
      "deleted" => "Tâche supprimée avec succès.",
      "activeTasks" => "La tâche ne peut pas être supprimée car elle contient des soumissions.",
    ],
    "user" => [
      "created" => "Utilisateur créé avec succès.",
      "updated" => "Utilisateur mis à jour avec succès.",
      "deleted" => "Utilisateur supprimé avec succès.",
      "approve" => "L'inscription de l'enseignant a été approuvée avec succès.",
    ]
  ],
  "setting" => [
    "profile" => [
      "updated" => "Profil mis à jour avec succès.",
    ],
    "security" => [
      "updated" => "Mot de passe mis à jour avec succès.",
    ]
  ],
  "student" => [
    "classroom" => [
      "enrolled" => "Inscription à la classe réussie !",
      "alreadyEnrolled" => "Vous êtes déjà inscrit dans cette classe !",
    ],
    "submission" => [
      "deadlineOver" => "La date limite pour cette tâche est dépassée.",
      "unfinishedPrevious" => "Votre soumission précédente est toujours en cours de traitement. Veuillez patienter.",
      "created" => "Soumission créée avec succès ! Veuillez patienter pendant que l'IA traite vos commentaires."
    ]
  ],
  "teacher" => [
    "classroom" => [
      "created" => "Classe créée avec succès !",
      "updated" => "Classe mise à jour avec succès !",
      "deleted" => "Classe supprimée avec succès !",
    ],
    "task" => [
      "created" => "Tâche créée avec succès !",
      "updated" => "Tâche mise à jour avec succès !",
      "deleted" => "Tâche supprimée avec succès !",
      "publish" => "Tâche publiée avec succès !",
      "unpublish" => "Tâche dépubliée avec succès !",
      "published" => "Vous ne pouvez pas :action une tâche publiée !",
      "alreadyPublished" => "La tâche est déjà publiée !",
      "hasSubmission" => "La tâche ne peut pas être :action car elle contient des soumissions !",
      "notPublished" => "La tâche n'est pas publiée !"
    ]
  ]
];
