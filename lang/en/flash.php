<?php

return [
  "admin" => [
    "classroom" => [
      "deleted" => "Classroom deleted successfully.",
      "activeTasks" => "Classroom cannot be deleted because it has active tasks.",
    ],
    "task" => [
      "deleted" => "Task deleted successfully.",
      "activeTasks" => "Task cannot be deleted because it has submissions.",
    ],
    "user" => [
      "created" => "User created successfully.",
      "updated" => "User updated successfully.",
      "deleted" => "User deleted successfully.",
      "approve" => "Teacher registration approved successfully.",
    ]
  ],
  "setting" => [
    "profile" => [
      "updated" => "Profile updated successfully.",
    ],
    "security" => [
      "updated" => "Password updated successfully.",
    ]
  ],
  "student" => [
    "classroom" => [
      "enrolled" => "Successfully enrolled in the classroom!",
      "alreadyEnrolled" => "You are already enrolled in this classroom!",
    ],
    "submission" => [
      "deadlineOver" => "The deadline for this task has passed.",
      "unfinishedPrevious" => "Your previous submission is still being processed. Please wait.",
      "created" => "Submission created successfully! Please wait while AI processes your feedback."
    ]
  ],
  "teacher" => [
    "classroom" => [
      "created" => "Classroom created successfully!",
      "updated" => "Classroom updated successfully!",
      "deleted" => "Classroom deleted successfully!",
    ],
    "task" => [
      "created" => "Task created successfully!",
      "updated" => "Task updated successfully!",
      "deleted" => "Task deleted successfully!",
      "publish" => "Task published successfully!",
      "unpublish" => "Task unpublished successfully!",
      "published" => "You cannot :action a published task!",
      "alreadyPublished" => "Task is already published!",
      "hasSubmission" => "Task cannot be :action because it has submissions!",
      "notPublished" => "Task is not published!"
    ]
  ]
];